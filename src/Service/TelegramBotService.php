<?php

namespace App\Service;

use App\Service\ExceptionHandle\TelegramApiException;
use App\Service\ExceptionHandle\TelegramBotApiException;
use CURLFile;
use Exception;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Telegram\Bot\Objects\Update;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\ChatMember;
use TelegramBot\Api\Types\Message;
use Throwable;

#[WithMonologChannel('webhook_payload')]
class TelegramBotService
{
    private BotApi $telegram;

    public function __construct(
        private LoggerInterface $logger,
        private string $botToken,
        private int $maxRetries = 3,
        private int $baseBackoffMs = 500
    )
    {
        $this->initBotApi();
    }

    private function initBotApi(): void
    {
        try {
            $this->telegram = new BotApi($this->botToken);
        } catch (Exception $e) {
            $this->logger->error('Telegram BotApi initialization failed', [
                'error' => $e->getMessage(),
            ]);

            throw TelegramBotApiException::connectionFailed($e->getMessage());
        }
    }

    /**
     * Sync sendMessage with retry + backoff.
     *
     * @param int $chatId
     * @param string $text
     * @param string|null $parseMode
     * @param bool $disablePreview
     * @param int|null $replyToMessageId
     * @param null $replyMarkup
     * @param bool $disableNotification
     * @param int|null $messageThreadId
     * @param bool|null $protectContent
     * @param bool|null $allowSendingWithoutReply
     * @return Message - return Telegram Message Type
     */
    public function sendMessage(
        int $chatId,
        string $text,
        ?string $parseMode = null,
        bool $disablePreview = false,
        ?int $replyToMessageId = null,
        $replyMarkup = null,
        bool $disableNotification = false,
        ?int $messageThreadId = null,
        ?bool $protectContent = null,
        ?bool $allowSendingWithoutReply = null
    ): Message {
        $attempt = 0;

        while (true) {
            try {
                $attempt++;
                return $this->telegram->sendMessage(
                    $chatId,
                    $text,
                    $parseMode,
                    $disablePreview,
                    $replyToMessageId,
                    $replyMarkup,
                    $disableNotification,
                    $messageThreadId,
                    $protectContent,
                    $allowSendingWithoutReply
                );
            } catch (Throwable $e) {
                $this->logger->error('Telegram sendMessage failed', [
                    'chatId' => $chatId,
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);

                if ($attempt >= $this->maxRetries) {
                    $this->logger->critical('Telegram sendMessage final failure', [
                        'chatId' => $chatId,
                        'attempts' => $attempt,
                    ]);
                    throw TelegramApiException::sendMessageFailed();
                }

                $backoffMs = $this->baseBackoffMs * (2 ** ($attempt - 1));
                $jitter = rand(0, (int)($backoffMs * 0.2));

                usleep(($backoffMs + $jitter) * 1000);
            }
        }
    }

    public function isSubscribed(int $chatId, string $channel = "@PAKETAGAME"): bool
    {
        try {
            $member = $this->telegram->getChatMember($channel, $chatId);

            return in_array($member->getStatus(), [
                'member',
                'creator',
                'administrator'
            ], true);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param $chatId
     * @param CURLFile $photo
     * @param string|null $caption
     * @param int|null $replyToMessageId
     * @param $replyMarkup
     * @param bool $disableNotification
     * @param string|null $parseMode
     * @param int|null $messageThreadId
     * @param bool|null $protectContent
     * @param bool|null $allowSendingWithoutReply
     * @return Message
     */
    public function sendPhoto(
        $chatId,
        CURLFile $photo,
        ?string $caption = null,
        ?int $replyToMessageId = null,
        $replyMarkup = null,
        bool $disableNotification = false,
        ?string $parseMode = null,
        ?int $messageThreadId = null,
        ?bool $protectContent = null,
        ?bool $allowSendingWithoutReply = null
    ): Message {
        $attempt = 0;

        while (true) {
            try {
                $attempt++;
                return $this->telegram->sendPhoto(
                    $chatId,
                    $photo,
                    $caption,
                    $replyToMessageId,
                    $replyMarkup,
                    $disableNotification,
                    $parseMode,
                    $messageThreadId,
                    $protectContent,
                    $allowSendingWithoutReply
                );
            } catch (Throwable $e) {
                $this->logger->error('Telegram sendPhoto failed', [
                    'chatId' => $chatId,
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);

                if ($attempt >= $this->maxRetries) {
                    $this->logger->critical('Telegram sendPhoto final failure', [
                        'chatId' => $chatId,
                        'attempts' => $attempt,
                    ]);
                    throw TelegramApiException::sendMessageFailed();
                }

                $backoffMs = $this->baseBackoffMs * (2 ** ($attempt - 1));
                $jitter = rand(0, (int)($backoffMs * 0.2));

                usleep(($backoffMs + $jitter) * 1000);
            }
        }
    }

    public function deleteMessage(int $chatId, int $messageId): void
    {
        try {
             $this->telegram->deleteMessage($chatId, $messageId);
        } catch (\TelegramBot\Api\Exception $e) {
            $this->logger->error($chatId, [$messageId, $e, ' Не удалось удалить сообщение, возможно оно уже было удалено.']);
        }
    }


    /**
     * @return Update[]
     * @throws \TelegramBot\Api\Exception
     * @throws \TelegramBot\Api\InvalidArgumentException
     */
    public function getUpdate(int $offset = 0): array
    {
        return $this->telegram->getUpdates($offset);
    }
}
