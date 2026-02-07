<?php

namespace App\Service\Telegram\Admin\Service;

use App\Repository\EventRepository;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Message\AdminGameMessage;
use App\Service\Telegram\Object\DeletableTelegramMessageInterface;
use App\Service\Telegram\TelegramMessageCache;
use DateTimeInterface;

class AdminGameService
{
    public function __construct(
        private AdminGameMessage $gameMessage,
        private EventRepository $eventRepository,
        private CreateEventService $createEventService,
        private ManageEventService $manageEventService,
        private TelegramMessageCache $cache,
    ) {
    }

    public function makeAction(int $chatId, DeletableTelegramMessageInterface $currentMessage): void
    {
        $events = $this->eventRepository->findCurrentAndNextEvents();

        $messageId = $this->gameMessage->sendMessage($chatId, $this->formatEvents($events));

        $currentMessage->delete($this->cache, $chatId);
        $this->cache->replaceMessage(TelegramCacheKey::START_MENU, $chatId, $messageId);
    }

    private function formatEvents(array $events): array
    {
        $normalize = function(array $list) {
            return array_map(function($event) {
                return [
                    'name' => $event['eventName'],
                    'from' => $event['periodFrom'] instanceof DateTimeInterface
                        ? $event['periodFrom']->format('d-m-Y H:i')
                        : null,
                    'to' => $event['periodTo'] instanceof DateTimeInterface
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

    public function createEvent(int $chatId, DeletableTelegramMessageInterface $currentMessage): void
    {
        $this->createEventService->sendMessage($chatId, $currentMessage);
    }

    public function manageEvent(int $chatId, DeletableTelegramMessageInterface $currentMessage): void
    {
        $this->manageEventService->sendEventListMessage($chatId, $currentMessage);
    }
}
