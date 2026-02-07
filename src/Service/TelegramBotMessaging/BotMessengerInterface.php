<?php

namespace App\Service\TelegramBotMessaging;

use CURLFile;
use TelegramBot\Api\Types\ChatMember;
use TelegramBot\Api\Types\ForceReply;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardRemove;

interface BotMessengerInterface
{
    /**
     * Send text message.
     */
    public function sendMessage(
        int $chatId,
        string $text,
        ?string $parseMode = null,
        bool $disablePreview = false,
        ?int $replyToMessageId = null,
        InlineKeyboardMarkup|ReplyKeyboardMarkup|ReplyKeyboardRemove|ForceReply|null $replyMarkup = null,
        bool $disableNotification = false,
        ?int $messageThreadId = null,
        ?bool $protectContent = null,
        ?bool $allowSendingWithoutReply = null
    ): Message;

    /**
     * Edit text of an existing message.
     *
     * @param int $chatId
     * @param int $messageId
     * @param string $text
     * @param string|null $parseMode
     * @param bool $disablePreview
     * @param InlineKeyboardMarkup|null $replyMarkup
     * @param string|null $inlineMessageId
     * @return Message|bool
     */
    public function editMessageText(
        int $chatId,
        int $messageId,
        string $text,
        ?string $parseMode = null,
        bool $disablePreview = false,
        InlineKeyboardMarkup|null $replyMarkup = null,
        ?string $inlineMessageId = null
    ): Message|bool;

    /**
     * Send photo.
     */
    public function sendPhoto(
        int|string $chatId,
        CURLFile|string $photo,
        ?string $caption = null,
        ?int $replyToMessageId = null,
        InlineKeyboardMarkup|ReplyKeyboardMarkup|ReplyKeyboardRemove|ForceReply|null $replyMarkup = null,
        bool $disableNotification = false,
        ?string $parseMode = null,
        ?int $messageThreadId = null,
        ?bool $protectContent = null,
        ?bool $allowSendingWithoutReply = null
    ): Message;

    /**
     * Send document.
     */
    public function sendDocument(
        int $chatId,
        CURLFile|string $document,
        ?string $caption = null,
        ?int $replyToMessageId = null,
        InlineKeyboardMarkup|ReplyKeyboardMarkup|ReplyKeyboardRemove|ForceReply|null $replyMarkup = null,
        bool $disableNotification = false,
        ?string $parseMode = null,
        ?int $messageThreadId = null,
        ?bool $protectContent = null,
        ?bool $allowSendingWithoutReply = null,
        CURLFile|string|null $thumbnail = null
    ): Message;

    /**
     * Delete message.
     */
    public function deleteMessage(int $chatId, int $messageId): bool;

    /**
     * Check chat subscription.
     */
    public function isSubscribed(int $chatId, string $channel): bool;

    /**
     * Get chat member info.
     */
    public function getChatMember(int|string $chatId, int $userId): ChatMember;
}
