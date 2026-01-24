<?php

namespace App\Service\Telegram\Admin\Service;

use App\Entity\Event;
use App\Repository\EventRepository;
use App\Repository\TelegramEventGroupRepository;
use App\Service\Telegram\Context\ContextStorage;
use App\Service\Telegram\Context\Dto\ManageEventContext;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Message\ManageEventMessage;
use App\Service\Telegram\TelegramMessageCache;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class ManageEventService
{
    public function __construct(
        private ManageEventMessage $manageEventMessage,
        private EventRepository $eventRepository,
        private TelegramEventGroupRepository $telegramEventGroupRepository,
        private TelegramMessageCache $cache,
        private ContextStorage $contextStorage,
        private EntityManagerInterface $entityManager,
        private AdminMenuService $adminMenuService,
    ) {
    }

    public function sendEventListMessage(int $chatId, int $currentMessage = 0, int $page = 1): void
    {
        $events = $this->eventRepository->findAll();

        if (empty($events)) {
            $errorMessage = "❌ Нет доступных событий для управления.";
            $errorId = $this->manageEventMessage->sendErrorMessage($chatId, $errorMessage);
            $this->cache->deleteCurrentMessage($chatId, $currentMessage);
            $this->cache->replaceMessage(TelegramCacheKey::STEP, $chatId, $errorId);
            return;
        }

        // Create context for event management
        $context = new ManageEventContext($chatId);
        $context->setBlockContext(true);
        $this->contextStorage->setContext($chatId, $context);

        $messageId = $this->manageEventMessage->sendEventListMessage($chatId, $events, $page);

        $this->cache->replaceMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId, $messageId);
        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
        $this->cache->deletePreviousMessage(TelegramCacheKey::START_MENU, $chatId);
        $this->cache->deleteCurrentMessage($chatId, $currentMessage);
    }

    public function eventPageAction(int $chatId, int $messageId, int $page): void
    {
        $events = $this->eventRepository->findAll();

        $this->manageEventMessage->editEventListMessage($chatId, $messageId, $events, $page);
    }

    public function selectEventAction(int $chatId, int $currentMessage, int $eventId): void
    {
        $event = $this->eventRepository->find($eventId);

        if (!$event) {
            $errorMessage = "❌ Событие не найдено.";
            $errorId = $this->manageEventMessage->sendErrorMessage($chatId, $errorMessage);
            $this->cache->deleteCurrentMessage($chatId, $currentMessage);
            $this->cache->replaceMessage(TelegramCacheKey::STEP, $chatId, $errorId);
            return;
        }

        // Set context
        $context = new ManageEventContext($chatId);
        $context->setEventId($eventId);
        $context->setEventName($event->getName());
        $context->setBlockContext(true);
        $this->contextStorage->setContext($chatId, $context);

        // Send event details
        $messageId = $this->manageEventMessage->sendEventDetailsMessage($chatId, $event);

        $this->cache->deleteCurrentMessage($chatId, $currentMessage);
        $this->cache->replaceMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId, $messageId);
    }

    public function toggleActiveAction(int $chatId, int $messageId, ManageEventContext $context): void
    {
        $event = $this->eventRepository->find($context->getEventId());

        if (!$event) {
            return;
        }

        $event->setIsActive(!$event->isActive());
        $this->entityManager->flush();

        $this->manageEventMessage->editEventDetailsMessage($chatId, $messageId, $event);
    }

    public function showDeleteConfirmationAction(int $chatId, ManageEventContext $context): void
    {
        $event = $this->eventRepository->find($context->getEventId());

        if (!$event) {
            return;
        }

        $context->setAction('delete_confirmation');
        $this->contextStorage->updateContext($chatId, $context);

        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);

        $this->manageEventMessage->editToDeleteConfirmation($chatId, $messageId, $event);
    }

    public function confirmDeleteAction(int $chatId, int $messageId, ManageEventContext $context): void
    {
        $event = $this->eventRepository->find($context->getEventId());

        if ($event) {
            $this->entityManager->remove($event);
            $this->entityManager->flush();
        }

        // Clear context
        $this->contextStorage->unsetContext($chatId);

        // Return to event list
        $this->cache->deleteCurrentMessage($chatId, $messageId);
        $this->sendEventListMessage($chatId, 0, 1);
    }

    public function cancelDeleteAction(int $chatId, int $messageId, ManageEventContext $context): void
    {
        $event = $this->eventRepository->find($context->getEventId());

        if (!$event) {
            return;
        }

        $context->setAction(null);
        $this->contextStorage->updateContext($chatId, $context);

        $this->manageEventMessage->editEventDetailsMessage($chatId, $messageId, $event);
    }

    public function showEditMenuAction(int $chatId, ManageEventContext $context): void
    {
        $event = $this->eventRepository->find($context->getEventId());

        if (!$event) {
            return;
        }

        $context->setAction('edit');
        $this->contextStorage->updateContext($chatId, $context);

        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);

        $this->manageEventMessage->editToEditMenu($chatId, $messageId, $event);
    }

    public function editTitleAction(int $chatId, ManageEventContext $context): void
    {
        $event = $this->eventRepository->find($context->getEventId());

        if (!$event) {
            return;
        }

        $context->setEditField('title');
        $context->setBlockContext(false);
        $this->contextStorage->updateContext($chatId, $context);

        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);

        $this->manageEventMessage->editToTitleEdit($chatId, $messageId, $event);
    }

    public function editStartDateAction(int $chatId, ManageEventContext $context): void
    {
        $event = $this->eventRepository->find($context->getEventId());

        if (!$event) {
            return;
        }

        $context->setEditField('start_date');
        $context->setBlockContext(false);
        $this->contextStorage->updateContext($chatId, $context);

        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);

        $this->manageEventMessage->editToStartDateEdit($chatId, $messageId, $event);
    }

    public function editEndDateAction(int $chatId, ManageEventContext $context): void
    {
        $event = $this->eventRepository->find($context->getEventId());

        if (!$event) {
            return;
        }

        $context->setEditField('end_date');
        $context->setBlockContext(false);
        $this->contextStorage->updateContext($chatId, $context);

        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);

        $this->manageEventMessage->editToEndDateEdit($chatId, $messageId, $event);
    }

    public function editPartnerLinkAction(int $chatId, ManageEventContext $context): void
    {
        $event = $this->eventRepository->find($context->getEventId());

        if (!$event) {
            return;
        }

        $context->setEditField('partner_link');
        $context->setBlockContext(false);
        $this->contextStorage->updateContext($chatId, $context);

        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);

        $this->manageEventMessage->editToPartnerLinkEdit($chatId, $messageId, $event);
    }

    public function editGroupAction(int $chatId, ManageEventContext $context): void
    {
        $event = $this->eventRepository->find($context->getEventId());

        if (!$event) {
            return;
        }

        $availableGroups = $this->telegramEventGroupRepository->findAvailableGroups();

        // Exclude current group from the list
        if ($event->getEventGroup()) {
            $currentGroupId = $event->getEventGroup()->getId();
            $availableGroups = array_filter($availableGroups, function($group) use ($currentGroupId) {
                return $group->getId() !== $currentGroupId;
            });
            $availableGroups = array_values($availableGroups); // Re-index array
        }

        if (empty($availableGroups)) {
            $errorMessage = "❌ Нет других доступных групп для выбора.";
            $errorId = $this->manageEventMessage->sendErrorMessage($chatId, $errorMessage);
            $this->cache->deleteCurrentMessage($chatId, $errorId);
            return;
        }

        $context->setEditField('group');
        $context->setBlockContext(true);
        $this->contextStorage->updateContext($chatId, $context);

        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);

        $this->manageEventMessage->editToGroupSelection($chatId, $messageId, $event, $availableGroups, 1);
    }

    public function groupPageAction(int $chatId, int $messageId, ManageEventContext $context, int $page): void
    {
        $event = $this->eventRepository->find($context->getEventId());

        if (!$event) {
            return;
        }

        $availableGroups = $this->telegramEventGroupRepository->findAvailableGroups();

        // Exclude current group from the list
        if ($event->getEventGroup()) {
            $currentGroupId = $event->getEventGroup()->getId();
            $availableGroups = array_filter($availableGroups, function($group) use ($currentGroupId) {
                return $group->getId() !== $currentGroupId;
            });
            $availableGroups = array_values($availableGroups);
        }

        $this->manageEventMessage->editToGroupSelection($chatId, $messageId, $event, $availableGroups, $page);
    }

    public function updateTitleAction(int $chatId, int $currentMessage, ManageEventContext $context, string $text): void
    {
        $event = $this->eventRepository->find($context->getEventId());

        if (!$event) {
            return;
        }

        $event->setName(trim($text));
        $this->entityManager->flush();

        $context->setEditField(null);
        $context->setBlockContext(true);
        $this->contextStorage->updateContext($chatId, $context);

        $contextMessageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->manageEventMessage->editEventDetailsMessage($chatId, $contextMessageId, $event);

        $this->cache->deleteCurrentMessage($chatId, $currentMessage);
    }

    public function updateStartDateAction(int $chatId, int $currentMessage, ManageEventContext $context, string $text): void
    {
        $result = $this->parseDateTimeString($text);

        if (!$result['success']) {
            $errorId = $this->manageEventMessage->sendErrorMessage($chatId, $result['error']);
            $this->cache->deleteCurrentMessage($chatId, $currentMessage);
            $this->cache->replaceMessage(TelegramCacheKey::STEP, $chatId, $errorId);
            return;
        }

        $event = $this->eventRepository->find($context->getEventId());

        if (!$event) {
            return;
        }

        $event->setPeriodFrom($result['datetime']);
        $this->entityManager->flush();

        $context->setEditField(null);
        $context->setBlockContext(true);
        $this->contextStorage->updateContext($chatId, $context);

        $contextMessageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->manageEventMessage->editEventDetailsMessage($chatId, $contextMessageId, $event);

        $this->cache->deleteCurrentMessage($chatId, $currentMessage);
    }

    public function updateEndDateAction(int $chatId, int $currentMessage, ManageEventContext $context, string $text): void
    {
        $result = $this->parseDateTimeString($text);

        if (!$result['success']) {
            $errorId = $this->manageEventMessage->sendErrorMessage($chatId, $result['error']);
            $this->cache->deleteCurrentMessage($chatId, $currentMessage);
            $this->cache->replaceMessage(TelegramCacheKey::STEP, $chatId, $errorId);
            return;
        }

        $event = $this->eventRepository->find($context->getEventId());

        if (!$event) {
            return;
        }

        $event->setPeriodTo($result['datetime']);
        $this->entityManager->flush();

        $context->setEditField(null);
        $context->setBlockContext(true);
        $this->contextStorage->updateContext($chatId, $context);

        $contextMessageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->manageEventMessage->editEventDetailsMessage($chatId, $contextMessageId, $event);

        $this->cache->deleteCurrentMessage($chatId, $currentMessage);
    }

    public function updatePartnerLinkAction(int $chatId, int $currentMessage, ManageEventContext $context, string $text): void
    {
        $event = $this->eventRepository->find($context->getEventId());

        if (!$event) {
            return;
        }

        $event->setPartnerChanelLink($text);
        $this->entityManager->flush();

        $context->setEditField(null);
        $context->setBlockContext(true);
        $this->contextStorage->updateContext($chatId, $context);

        $contextMessageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->manageEventMessage->editEventDetailsMessage($chatId, $contextMessageId, $event);

        $this->cache->deleteCurrentMessage($chatId, $currentMessage);
    }

    public function removePartnerLinkAction(int $chatId, ManageEventContext $context): void
    {
        $event = $this->eventRepository->find($context->getEventId());

        if (!$event) {
            return;
        }

        $event->setPartnerChanelLink(null);
        $this->entityManager->flush();

        $context->setEditField(null);
        $context->setBlockContext(true);
        $this->contextStorage->updateContext($chatId, $context);

        $contextMessageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->manageEventMessage->editEventDetailsMessage($chatId, $contextMessageId, $event);

        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
    }

    public function selectGroupAction(int $chatId, ManageEventContext $context, int $groupId): void
    {
        $event = $this->eventRepository->find($context->getEventId());
        $group = $this->telegramEventGroupRepository->find($groupId);

        if (!$event || !$group) {
            return;
        }

        $event->setEventGroup($group);
        $this->entityManager->flush();

        $context->setEditField(null);
        $context->setBlockContext(true);
        $this->contextStorage->updateContext($chatId, $context);

        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->manageEventMessage->editEventDetailsMessage($chatId, $messageId, $event);
    }

    public function closeGroupSelectionAction(int $chatId, ManageEventContext $context): void
    {
        $event = $this->eventRepository->find($context->getEventId());

        if (!$event) {
            return;
        }

        $context->setEditField(null);
        $context->setBlockContext(true);
        $this->contextStorage->updateContext($chatId, $context);

        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->manageEventMessage->editToEditMenu($chatId, $messageId, $event);
    }

    public function backToListAction(int $chatId, int $messageId): void
    {
        $this->contextStorage->unsetContext($chatId);

        $this->cache->deleteCurrentMessage($chatId, $messageId);
        $this->cache->delete(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);

        $this->sendEventListMessage($chatId, 0, 1);
    }

    public function backToDetailsAction(int $chatId, ManageEventContext $context): void
    {
        $event = $this->eventRepository->find($context->getEventId());

        if (!$event) {
            return;
        }

        $context->setAction(null);
        $context->setEditField(null);
        $context->setBlockContext(true);
        $this->contextStorage->updateContext($chatId, $context);

        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->manageEventMessage->editEventDetailsMessage($chatId, $messageId, $event);
    }

    public function backToEditMenuAction(int $chatId, ManageEventContext $context): void
    {
        $event = $this->eventRepository->find($context->getEventId());

        if (!$event) {
            return;
        }

        $context->setEditField(null);
        $context->setBlockContext(true);
        $this->contextStorage->updateContext($chatId, $context);

        $messageId = $this->cache->getMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->manageEventMessage->editToEditMenu($chatId, $messageId, $event);
    }

    public function backToEventsMenuAction(int $chatId, int $messageId, AdminMenuService $adminMenuService): void
    {
        $this->contextStorage->unsetContext($chatId);

        $this->cache->deleteCurrentMessage($chatId, $messageId);
        $this->cache->delete(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);

        $adminMenuService->handle($chatId);
    }

    public function emergencyExit(int $chatId, int $currentMessage): void
    {
        $this->contextStorage->unsetContext($chatId);

        $this->cache->deleteCurrentMessage($chatId, $currentMessage);
        $this->cache->deletePreviousMessage(TelegramCacheKey::CONTEXT_MESSAGE, $chatId);
        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);

        $this->adminMenuService->handle($chatId);
    }

    private function parseDateTimeString(string $input): array
    {
        $pattern = '/^(\d{2})-(\d{2})-(\d{4})\s+(\d{2}):(\d{2})$/';

        if (!preg_match($pattern, $input, $matches)) {
            return [
                'success' => false,
                'error' => '❌ Неверный формат даты. Используйте формат: дд-мм-гггг чч:мм',
            ];
        }

        [, $day, $month, $year, $hour, $minute] = $matches;

        // Validate date components
        if (!checkdate($month, $day, $year)) {
            return [
                'success' => false,
                'error' => '❌ Неверная дата. Проверьте количество дней в месяце.',
            ];
        }

        if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
            return [
                'success' => false,
                'error' => '❌ Неверное время. Часы должны быть 00-23, минуты 00-59.',
            ];
        }

        try {
            $datetime = DateTimeImmutable::createFromFormat(
                'd-m-Y H:i',
                "$day-$month-$year $hour:$minute"
            );

            if (!$datetime) {
                return [
                    'success' => false,
                    'error' => '❌ Не удалось создать дату. Проверьте введенные данные.',
                ];
            }

            return [
                'success' => true,
                'datetime' => $datetime,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => '❌ Ошибка при создании даты: ' . $e->getMessage(),
            ];
        }
    }
}
