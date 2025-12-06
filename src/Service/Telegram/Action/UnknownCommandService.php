<?php

namespace App\Service\Telegram\Action;

use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Security\SecurityTelegramUserService;
use App\Service\Telegram\TelegramMessageCache;
use App\Service\TelegramBotService;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class UnknownCommandService
{
    public function __construct(
        private TelegramBotService $telegramBotService,
        private SecurityTelegramUserService $security,
        private TelegramMessageCache $cache,
    ) {
    }

    public function handle(): void
    {
        $user = $this->security->fetchCurrentUser();
        $chatId = $user->getChatId();

        $text = <<<MARKDOWN
        😅 Ой! Кажется такой команды нет.
        MARKDOWN;

        $replyKeyboard = new ReplyKeyboardMarkup(
            [
                ['Вернуться в меню'],
            ],
            true,
            true,
            true
        );

        $message = $this->telegramBotService->sendMessage(
            $chatId,
            $text,
            'Markdown',
            false,
            null,
            $replyKeyboard
        );

        $this->cache->saveAndCleanup('step', $chatId, $message->getMessageId());
    }

    public function handleCallbackQuery(CallbackQueryTelegramPayload $dto): void
    {
        $from = $dto->getChatId();
        if (in_array($from, [-1002900822842])) {
            return;
        }

        $this->handle();
    }
}
