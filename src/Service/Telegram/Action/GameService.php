<?php

namespace App\Service\Telegram\Action;

use App\Security\SecurityTelegramUserService;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Enum\TelegramParseMode;
use App\Service\Telegram\TelegramMessageCache;
use App\Service\TelegramBotService;
use CURLFile;

class GameService
{
    public function __construct(
        private TelegramMessageCache $cache,
        private SecurityTelegramUserService $security,
        private TelegramBotService $telegramBotService,
    ) {
    }

    public function handle(int $currentMessage): void
    {
        $user = $this->security->fetchCurrentUser();
        $chatId = $user->getChatId();

        $caption = <<<MARKDOWN
        👋 *Заходи в наш игровой* [канал](https://t.me/PAKETAGAME) *и учавствуй в играх!*
        MARKDOWN;

        $photoPath = '/app/public/image/game.jpg';

        $message = $this->telegramBotService->sendPhoto(
            $chatId,
            new CURLFile($photoPath),
            $caption,
            null,
            null,
            false,
            TelegramParseMode::MARKDOWN,
        );

        $this->cache->clear(TelegramCacheKey::STEP, $chatId, $currentMessage, $message->getMessageId());
    }
}
