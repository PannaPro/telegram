<?php

namespace App\Service\TelegramBotMessaging;

use App\Service\ExceptionHandler\TelegramApiException;
use App\Service\ExceptionHandler\TelegramBotApiException;
use CURLFile;
use Exception;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception as TelegramBotException;
use TelegramBot\Api\Types\ChatMember;
use TelegramBot\Api\Types\Message;
use Throwable;

#[WithMonologChannel('action')]
class TelegramBotService implements BotMessengerInterface
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

    public function editMessageText(
        int $chatId,
        int $messageId,
        string $text,
        ?string $parseMode = null,
        bool $disablePreview = false,
        $replyMarkup = null,
        ?string $inlineMessageId = null
    ): Message|bool
    {
        try {
            return $this->telegram->editMessageText(
                $chatId,
                $messageId,
                $text,
                $parseMode,
                $disablePreview,
                $replyMarkup,
            );
        } catch (Exception $e) {
            if (!str_contains($e->getMessage(), 'message is not modified')) {
                $this->logger->error($chatId, [$e->getMessage()]);
            }

            return true;
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

        $this->logger->info('Sending message to Telegram', [
            'chatId' => $chatId,
            'textLength' => strlen($text),
            'parseMode' => $parseMode,
        ]);

        while (true) {
            try {
                $attempt++;
                $message = $this->telegram->sendMessage(
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

                $this->logger->info('Message sent successfully', [
                    'chatId' => $chatId,
                    'messageId' => $message->getMessageId(),
                    'attempt' => $attempt,
                ]);

                return $message;
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
            $member = $this->getChatMember($channel, $chatId);

            return in_array($member->getStatus(), [
                'member',
                'creator',
                'administrator'
            ], true);
        } catch (Exception) {
            return false;
        }
    }

    /**
     * @param $chatId
     * @param CURLFile|string $photo
     * @param string|null $caption
     * @param int|null $replyToMessageId
     * @param null $replyMarkup
     * @param bool $disableNotification
     * @param string|null $parseMode
     * @param int|null $messageThreadId
     * @param bool|null $protectContent
     * @param bool|null $allowSendingWithoutReply
     * @return Message
     */
    public function sendPhoto(
        $chatId,
        CURLFile|string $photo,
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

        $this->logger->info('Sending photo to Telegram', [
            'chatId' => $chatId,
            'captionLength' => $caption ? strlen($caption) : 0,
            'parseMode' => $parseMode,
        ]);

        while (true) {
            try {
                $attempt++;
                $message = $this->telegram->sendPhoto(
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

                $this->logger->info('Photo sent successfully', [
                    'chatId' => $chatId,
                    'messageId' => $message->getMessageId(),
                    'attempt' => $attempt,
                ]);

                return $message;
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

    /**
     * @param int $chatId
     * @param CURLFile|string $document
     * @param string|null $caption
     * @param null $replyToMessageId
     * @param null $replyMarkup
     * @param bool $disableNotification
     * @param string|null $parseMode
     * @param int|null $messageThreadId
     * @param bool|null $protectContent
     * @param bool|null $allowSendingWithoutReply
     * @param CURLFile|string|null $thumbnail
     * @return Message
     */
    public function sendDocument(
        int $chatId,
        CURLFile|string $document,
        string $caption = null,
        $replyToMessageId = null,
        $replyMarkup = null,
        bool $disableNotification = false,
        ?string $parseMode = null,
        ?int $messageThreadId = null,
        ?bool $protectContent = null,
        ?bool $allowSendingWithoutReply = null,
        CURLFile|string|null $thumbnail = null
    ): Message
    {
        $attempt = 0;

        $this->logger->info('Sending document to Telegram', [
            'chatId' => $chatId,
            'captionLength' => $caption ? strlen($caption) : 0,
            'parseMode' => $parseMode,
        ]);

        while (true) {
            try {
                $attempt++;
                $message = $this->telegram->sendDocument(
                    $chatId,
                    $document,
                    $caption,
                    $replyToMessageId,
                    $replyMarkup,
                    $disableNotification,
                    $parseMode
                );

                $this->logger->info('Document sent successfully', [
                    'chatId' => $chatId,
                    'messageId' => $message->getMessageId(),
                    'attempt' => $attempt,
                ]);

                return $message;
            } catch (Throwable $e) {
                $this->logger->error('Telegram sendDocument failed', [
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

    public function deleteMessage(int $chatId, int $messageId): bool
    {
        if ($messageId === 0) {
            return true;
        }

        try {
             return $this->telegram->deleteMessage($chatId, $messageId);
        } catch (TelegramBotException) {
            $this->logger->error("Не удалось удалить сообщение $messageId для чата $chatId, возможно оно уже было удалено.", );
            return false;
        }
    }

    /**
     * Генерирует одноразовую инвайт-ссылку в закрытую группу
     *
     * @param int|string $chatId ID группы или @username
     * @param int $ttlSeconds Время жизни ссылки в секундах (по умолчанию 10 минут)
     *
     * @return string
     * @throws \Exception
     */
    public function createOneTimeInviteLink(int|string $chatId, int $ttlSeconds = 600): string
    {
        $expireAt = time() + $ttlSeconds;

        $chatId = -1003348099183;
        $response = $this->telegram->call('createChatInviteLink', [
            'chat_id'     => $chatId,
            'expire_date' => $expireAt,
            'member_limit'=> 1, // 🔥 одноразовая ссылка
        ]);

        if (!isset($response['invite_link'])) {
            throw new \RuntimeException('Не удалось создать инвайт-ссылку');
        }

        return $response['invite_link'];
    }

    public function getUpdates(int $offset = 0, int $limit = 100, int $timeout = 0): array
    {
        try {
            $response = $this->telegram->call('getUpdates', [
                'offset' => $offset,
                'limit' => $limit,
                'timeout' => $timeout,
                'allowed_updates' => ['message', 'callback_query', 'my_chat_member'],
            ]);

            $this->logger->debug('Got updates from Telegram', ['count' => count($response)]);

            return $response;
        } catch (TelegramBotException $e) {
            $this->logger->error('Failed to get updates', ['error' => $e->getMessage()]);
            throw TelegramApiException::sendMessageFailed();
        }
    }

    public function getBotToken(): string
    {
        return $this->botToken;
    }

    public function getChatMember(int|string $chatId, int $userId): ChatMember
    {
        return $this->telegram->getChatMember($chatId, $userId);
    }

    public function answerCallbackQuery(
        int $callbackQueryId,
        string $text = null,
        bool $showAlert = false,
        string $url = null,
        int $cacheTime = 0
    ): bool
    {
        return $this->telegram->answerCallbackQuery(
            $callbackQueryId,
            $text,
            $showAlert,
            $url,
            $cacheTime
        );
    }
}
