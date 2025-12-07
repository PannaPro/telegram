<?php

namespace App\Service\Telegram\Action;

use App\Security\SecurityTelegramUserService;
use App\Service\Telegram\TelegramMessageCache;
use App\Service\TelegramBotService;
use CURLFile;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

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
            'Markdown',
        );

        $this->cache->clear('step', $chatId, $currentMessage, $message->getMessageId());
    }
}
