<?php

namespace App\Service\Telegram\Common;

use App\Http\Dto\MessageTelegramPayload;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Message\UnknownCommandMessage;
use App\Service\Telegram\TelegramMessageCache;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;

#[WithMonologChannel('action')]
class UnknownCommandService
{
    public function __construct(
        private TelegramMessageCache $cache,
        private LoggerInterface $logger,
        private UnknownCommandMessage $message,
    ) {
    }

    public function makeAction(MessageTelegramPayload $payload): void
    {
        $text = $payload->getText();
        $chatId = $payload->getChatId();
        $currentMessage = $payload->getMessageId();

        $this->logger->debug("Не известный message $text, вызван $chatId");

        $messageId = $this->message->sendMessage($chatId);

        $this->cache->deleteCurrentMessage($chatId, $currentMessage);
        $this->cache->replaceMessage(TelegramCacheKey::STEP, $chatId, $messageId);
    }

    public function handleCallbackQuery(int $chatId, string $data): void
    {
        $this->logger->debug("Не известный колбек $data, вызван $chatId");

        // TODO не отправлять сообщения в каналы
        if ($chatId < 0) {
            return;
        }

        $messageId = $this->message->sendMessage($chatId);

        $this->cache->replaceMessage(TelegramCacheKey::STEP, $chatId, $messageId);
    }
}
