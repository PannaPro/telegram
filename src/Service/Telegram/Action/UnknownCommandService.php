<?php

namespace App\Service\Telegram\Action;

use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Http\Dto\MessageTelegramPayload;
use App\Security\SecurityTelegramUserService;
use App\Service\Telegram\TelegramMessageCache;
use App\Service\TelegramBotService;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

#[WithMonologChannel('action')]
class UnknownCommandService
{
    public function __construct(
        private TelegramBotService $telegramBotService,
        private SecurityTelegramUserService $security,
        private TelegramMessageCache $cache,
        private LoggerInterface $logger,
    ) {
    }

    public function handle(MessageTelegramPayload $dto): void
    {
        $text = $dto->getText();
        $chatId = $dto->getChatId();

        $this->logger->debug("Не известный message $text, вызван $chatId");

        $this->sendMessage();
    }

    public function handleCallbackQuery(CallbackQueryTelegramPayload $dto): void
    {
        $text = $dto->getText();
        $chatId = $dto->getChatId();

        $this->logger->debug("Не известный колбек $text, вызван $chatId");

        // TODO не отправлять сообщения в каналы
        if ($chatId < 0) {
            return;
        }

        $this->sendMessage();
    }

    private function sendMessage(): void
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
}
