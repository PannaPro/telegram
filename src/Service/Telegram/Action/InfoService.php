<?php

namespace App\Service\Telegram\Action;

use App\Http\Dto\MessageTelegramPayload;
use App\Service\Telegram\TelegramMessageCache;
use App\Service\TelegramBotService;
use CURLFile;

final class InfoService
{
    public function __construct(
        private TelegramBotService $telegramBotService,
        private TelegramMessageCache $cache,
    ) {
    }

    public function handle(MessageTelegramPayload $dto): void
    {
        $type = 'info';
        $chatId = $dto->getChatId();

        $photoPath = '/app/public/image/pipe.jpg';

        $message = $this->telegramBotService->sendPhoto(
            $chatId,
            new CURLFile($photoPath),
            "Инфо",
            null,
            null,
            false,
            'Markdown'
        );

        $this->cache->saveAndCleanup($type, $chatId, $message->getMessageId());
    }
}
