<?php

namespace App\Service\Telegram;

use App\Http\Dto\MessageTelegramPayload;
use App\Security\SecurityTelegramUserService;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Enum\TelegramParseMode;
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

        $this->sendMessage($dto->getMessageId());
    }

    public function handleCallbackQuery(int $chatId, string $data): void
    {
        $this->logger->debug("Не известный колбек $data, вызван $chatId");

        // TODO не отправлять сообщения в каналы
        if ($chatId < 0) {
            return;
        }

        $this->sendMessage();
    }

    private function sendMessage(int $currentMessage = 0): void
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
            TelegramParseMode::MARKDOWN,
            false,
            null,
            $replyKeyboard
        );

        $this->cache->clear(TelegramCacheKey::STEP, $chatId, $currentMessage, $message->getMessageId());
    }
}
