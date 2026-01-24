<?php

namespace App\Service\Telegram\Admin\Service;

use App\Entity\Event;
use App\Repository\EventRepository;
use App\Repository\EventTypeRepository;
use App\Repository\TelegramEventGroupRepository;
use App\Service\Telegram\Context\ContextStorage;
use App\Service\Telegram\Context\Dto\CreateEventContext;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Handler\AnswerCallbackQueryTrait;
use App\Service\Telegram\Message\AdminGameMessage;
use App\Service\Telegram\Message\CreateEventMessage;
use App\Service\Telegram\TelegramMessageCache;
use App\Service\TelegramBotService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

readonly class CreateEventService
{
    use AnswerCallbackQueryTrait;

    public function __construct(
        private TelegramMessageCache $cache,
        private TelegramBotService $bot,
        private ContextStorage $contextStorage,
        private AdminMenuService $adminMenuService,
        private CreateEventMessage $eventMessage,
        private EventTypeRepository $eventTypeRepository,
        private EventRepository $eventRepository,
        private TelegramEventGroupRepository $telegramEventGroupRepository,
        private EntityManagerInterface $entityManager,
        private AdminGameMessage $adminGameMessage,
    ) {
    }

    public function sendMessage(int $chatId, int $currentMessage = 0): int
    {
        // Check if there are available groups before starting event creation
        $availableGroups = $this->telegramEventGroupRepository->findAvailableGroups();
        if (empty($availableGroups)) {
            $errorMessage = "Для создания игры нет доступной группы. Добавьте бота в группу и сделайте его админом.";
            $errorId = $this->eventMessage->sendErrorMessage($chatId, $errorMessage);

            $this->cache->deleteCurrentMessage($chatId, $currentMessage);
            $this->cache->replaceMessage(TelegramCacheKey::STEP, $chatId, $errorId);

            return 0;
        }

        $eventTypes = $this->eventTypeRepository->findAll();

        $context = new CreateEventContext($chatId);
        $this->contextStorage->setContext($chatId, $context);

        $messageId = $this->eventMessage->sendEventTypeMessage($chatId, $eventTypes);

        $this->cache->replaceMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId, $messageId);
        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
        $this->cache->deletePreviousMessage(TelegramCacheKey::START_MENU, $chatId);
        $this->cache->deleteCurrentMessage($chatId, $currentMessage);

        return $messageId;
    }

    public function selectEventTypeAction(int $chatId, int $callbackId, CreateEventContext $context, int $eventTypeId): void
    {
        $this->answerCallbackQuery($callbackId, 'категория выбрана');

        $eventType = $this->eventTypeRepository->find($eventTypeId);
        if (!$eventType) {
            $this->answerCallbackQuery($callbackId, 'категория не найдена');
            return;
        }

        $context->setEventTypeId($eventTypeId);
        $context->setEventTypeName($eventType->getName());
        $context->setStep(2);
        $this->contextStorage->updateContext($chatId, $context);

        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->eventMessage->editEventNameMessage($chatId, $messageId, $context->getFormattedText());
        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
    }

    public function eventNameAction(int $chatId, int $currentMessage, CreateEventContext $context, string $name): void
    {
        $context->setName($name);
        $context->setStep(3);
        $this->contextStorage->updateContext($chatId, $context);

        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->eventMessage->editEventDatesMessage($chatId, $messageId, $context->getFormattedText());
        $this->cache->deleteCurrentMessage($chatId, $currentMessage);
        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
    }

    public function eventStartDateAction(int $chatId, int $currentMessage, CreateEventContext $context, string $dateTimeString): void
    {
        $result = $this->parseDateTimeString($dateTimeString);
        if (!$result['success']) {
            $errorId = $this->eventMessage->sendErrorMessage($chatId, $result['error']);
            $this->cache->deleteCurrentMessage($chatId, $currentMessage);
            $this->cache->replaceMessage(TelegramCacheKey::STEP, $chatId, $errorId);
            return;
        }

        $context->setPeriodFromDate($result['date']);
        $context->setPeriodFromTime($result['time']);
        $context->setStep(4);
        $this->contextStorage->updateContext($chatId, $context);

        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->eventMessage->editEventEndDateMessage($chatId, $messageId, $context->getFormattedText());
        $this->cache->deleteCurrentMessage($chatId, $currentMessage);
        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
    }

    public function eventEndDateAction(int $chatId, int $currentMessage, CreateEventContext $context, string $dateTimeString): void
    {
        $result = $this->parseDateTimeString($dateTimeString);
        if (!$result['success']) {
            $errorId = $this->eventMessage->sendErrorMessage($chatId, $result['error']);
            $this->cache->deleteCurrentMessage($chatId, $currentMessage);
            $this->cache->replaceMessage(TelegramCacheKey::STEP, $chatId, $errorId);
            return;
        }

        $context->setPeriodToDate($result['date']);
        $context->setPeriodToTime($result['time']);

        // After dates, always go to group selection (step 5)
        $context->setStep(5);
        $this->contextStorage->updateContext($chatId, $context);

        $groups = $this->telegramEventGroupRepository->findAvailableGroups();
        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->eventMessage->editGroupSelectionMessage($chatId, $messageId, $context->getFormattedText(), $groups, 1);

        $this->cache->deleteCurrentMessage($chatId, $currentMessage);
        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
    }

    public function selectGroupAction(int $chatId, int $callbackId, CreateEventContext $context, int $groupId): void
    {
        $this->answerCallbackQuery($callbackId, 'группа выбрана');

        $group = $this->telegramEventGroupRepository->find($groupId);
        if (!$group) {
            $this->answerCallbackQuery($callbackId, 'группа не найдена');
            return;
        }

        $context->setTelegramEventGroupId($groupId);
        $context->setTelegramEventGroupTitle($group->getTitle());

        // Check if partner link is needed (only for Event type)
        if ($context->getEventTypeName() === 'Ивент') {
            $context->setStep(6);
            $this->contextStorage->updateContext($chatId, $context);
            $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
            $this->eventMessage->editPartnerLinkMessage($chatId, $messageId, $context->getFormattedText());
        } else {
            $context->setStep(7);
            $this->contextStorage->updateContext($chatId, $context);
            $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
            $this->eventMessage->editConfirmationMessage($chatId, $messageId, $context);
        }

        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
    }

    public function groupPageAction(int $chatId, int $callbackId, CreateEventContext $context, int $page): void
    {
        $this->answerCallbackQuery($callbackId);

        $groups = $this->telegramEventGroupRepository->findAvailableGroups();
        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->eventMessage->editGroupSelectionMessage($chatId, $messageId, $context->getFormattedText(), $groups, $page);
    }

    public function partnerLinkAction(int $chatId, int $currentMessage, CreateEventContext $context, string $link): void
    {
        $context->setPartnerChanelLink($link);
        $context->setStep(7);
        $this->contextStorage->updateContext($chatId, $context);

        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->eventMessage->editConfirmationMessage($chatId, $messageId, $context);
        $this->cache->deleteCurrentMessage($chatId, $currentMessage);
        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
    }

    public function skipPartnerLinkAction(int $chatId, int $callbackId, CreateEventContext $context): void
    {
        $this->answerCallbackQuery($callbackId, 'ссылка пропущена');
        $context->setStep(7);
        $this->contextStorage->updateContext($chatId, $context);

        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->eventMessage->editConfirmationMessage($chatId, $messageId, $context);
        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
    }

    public function confirmEventAction(int $chatId, int $callbackId, CreateEventContext $context): void
    {
        $this->answerCallbackQuery($callbackId, 'событие создано');

        $eventType = $this->eventTypeRepository->find($context->getEventTypeId());
        if (!$eventType) {
            $this->answerCallbackQuery($callbackId, 'ошибка при создании события');
            return;
        }

        $event = new Event();
        $event->setName($context->getName());
        $event->setType($eventType);

        // Parse and set dates
        $periodFrom = $this->parseFullDateTime($context->getPeriodFromDate(), $context->getPeriodFromTime());
        $periodTo = $this->parseFullDateTime($context->getPeriodToDate(), $context->getPeriodToTime());

        if ($periodFrom) {
            $event->setPeriodFrom($periodFrom);
        }
        if ($periodTo) {
            $event->setPeriodTo($periodTo);
        }

        $now = new DateTimeImmutable();
        if ($periodFrom && $periodFrom <= $now) {
            $event->setIsActive(true);
        } else {
            $event->setIsActive(false);
        }

        if ($context->getPartnerChanelLink()) {
            $event->setPartnerChanelLink($context->getPartnerChanelLink());
        }

        if ($context->getTelegramEventGroupId()) {
            $group = $this->telegramEventGroupRepository->find($context->getTelegramEventGroupId());
            if ($group) {
                $event->setEventGroup($group);
            }
        }

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $this->unsetContext($chatId);

        $this->cache->deletePreviousMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);

        $events = $this->eventRepository->findCurrentAndNextEvents();
        $messageId = $this->adminGameMessage->sendMessage($chatId, $this->formatEvents($events));
        $this->cache->replaceMessage(TelegramCacheKey::STEP, $chatId, $messageId);
    }

    public function editEventAction(int $chatId, int $callbackId, CreateEventContext $context): void
    {
        $this->answerCallbackQuery($callbackId, 'редактирование');

        $context->setStep(1);
        $this->contextStorage->updateContext($chatId, $context);

        $eventTypes = $this->eventTypeRepository->findAll();
        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->eventMessage->editEventTypeMessage($chatId, $messageId, $eventTypes);
        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
    }

    public function cancelEventAction(int $chatId, int $callbackId): void
    {
        $this->answerCallbackQuery($callbackId, 'создание события отменено');
        $this->unsetContext($chatId);

        // Return to Events menu
        $this->cache->deletePreviousMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);

        $events = $this->eventRepository->findCurrentAndNextEvents();
        $messageId = $this->adminGameMessage->sendMessage($chatId, $this->formatEvents($events));
        $this->cache->replaceMessage(TelegramCacheKey::STEP, $chatId, $messageId);
    }

    public function emergencyExit(int $chatId, int $currentMessage): void
    {
        $this->unsetContext($chatId);

        // Delete all messages and return to admin menu
        $this->cache->deleteCurrentMessage($chatId, $currentMessage);
        $this->cache->deletePreviousMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);

        $this->adminMenuService->handle($chatId);
    }

    public function backToTypeAction(int $chatId, int $callbackId, CreateEventContext $context): void
    {
        $this->answerCallbackQuery($callbackId);
        $context->setName(null);
        $context->setStep(1);
        $this->contextStorage->updateContext($chatId, $context);

        $eventTypes = $this->eventTypeRepository->findAll();
        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->eventMessage->editEventTypeMessage($chatId, $messageId, $eventTypes);
        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
    }

    public function backToNameAction(int $chatId, int $callbackId, CreateEventContext $context): void
    {
        $this->answerCallbackQuery($callbackId);
        $context->setPeriodFromDate(null);
        $context->setPeriodFromTime(null);
        $context->setStep(2);
        $this->contextStorage->updateContext($chatId, $context);

        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->eventMessage->editEventNameMessage($chatId, $messageId, $context->getFormattedText());
        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
    }

    public function backToStartDateAction(int $chatId, int $callbackId, CreateEventContext $context): void
    {
        $this->answerCallbackQuery($callbackId);
        $context->setPeriodToDate(null);
        $context->setPeriodToTime(null);
        $context->setStep(3);
        $this->contextStorage->updateContext($chatId, $context);

        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->eventMessage->editEventDatesMessage($chatId, $messageId, $context->getFormattedText());
        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
    }

    public function backToEndDateAction(int $chatId, int $callbackId, CreateEventContext $context): void
    {
        $this->answerCallbackQuery($callbackId);
        $context->setTelegramEventGroupId(null);
        $context->setTelegramEventGroupTitle(null);
        $context->setPartnerChanelLink(null);
        $context->setStep(4);
        $this->contextStorage->updateContext($chatId, $context);

        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->eventMessage->editEventEndDateMessage($chatId, $messageId, $context->getFormattedText());
        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
    }

    public function backToGroupSelectionAction(int $chatId, int $callbackId, CreateEventContext $context): void
    {
        $this->answerCallbackQuery($callbackId);
        $context->setPartnerChanelLink(null);
        $context->setStep(5);
        $this->contextStorage->updateContext($chatId, $context);

        $groups = $this->telegramEventGroupRepository->findAvailableGroups();
        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->eventMessage->editGroupSelectionMessage($chatId, $messageId, $context->getFormattedText(), $groups, 1);
        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
    }

    private function parseDateTimeString(string $input): array
    {
        $input = trim($input);

        if (!preg_match('/^(\d{2})-(\d{2})-(\d{4})\s+(\d{2}):(\d{2})$/', $input, $matches)) {
            return [
                'success' => false,
                'error' => '❌ Неверный формат даты и времени. Используйте: дд-мм-гггг чч:мм',
            ];
        }

        $day = (int)$matches[1];
        $month = (int)$matches[2];
        $year = (int)$matches[3];
        $hour = (int)$matches[4];
        $minute = (int)$matches[5];

        // Validate ranges
        if ($month < 1 || $month > 12 || $hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
            return [
                'success' => false,
                'error' => '❌ Неверные значения даты или времени.',
            ];
        }

        // Validate date using checkdate
        if (!checkdate($month, $day, $year)) {
            return [
                'success' => false,
                'error' => '❌ Неверная дата. Проверьте количество дней в месяце.',
            ];
        }

        $date = sprintf('%02d-%02d-%04d', $day, $month, $year);
        $time = sprintf('%02d:%02d', $hour, $minute);

        return [
            'success' => true,
            'date' => $date,
            'time' => $time,
        ];
    }

    private function parseFullDateTime(string $date, string $time): ?DateTimeImmutable
    {
        if (!preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $date, $dateMatches)) {
            return null;
        }

        if (!preg_match('/^(\d{2}):(\d{2})$/', $time, $timeMatches)) {
            return null;
        }

        $day = (int)$dateMatches[1];
        $month = (int)$dateMatches[2];
        $year = (int)$dateMatches[3];
        $hour = (int)$timeMatches[1];
        $minute = (int)$timeMatches[2];

        try {
            return new DateTimeImmutable(sprintf('%04d-%02d-%02d %02d:%02d:00', $year, $month, $day, $hour, $minute));
        } catch (\Exception $e) {
            return null;
        }
    }

    private function unsetContext($chatId): void
    {
        $this->contextStorage->unsetContext($chatId);
    }

    private function formatEvents(array $events): array
    {
        $normalize = function(array $list) {
            return array_map(function($event) {
                return [
                    'name' => $event['eventName'],
                    'from' => $event['periodFrom'] instanceof \DateTimeInterface
                        ? $event['periodFrom']->format('d-m-Y H:i')
                        : null,
                    'to' => $event['periodTo'] instanceof \DateTimeInterface
                        ? $event['periodTo']->format('d-m-Y H:i')
                        : null,
                    'isActive' => $event['isActive'],
                ];
            }, $list);
        };

        return [
            'current' => isset($events['current']) ? $normalize($events['current']) : [],
            'upcoming' => isset($events['upcoming']) ? $normalize($events['upcoming']) : [],
            'totalUpcoming' => $events['totalUpcoming']
        ];
    }
}
