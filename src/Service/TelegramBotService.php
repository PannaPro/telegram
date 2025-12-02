<?php

namespace App\Service;

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;


class TelegramBotService
{
    private BotApi $telegram;

    public function __construct(private string $botToken)
    {
        $this->telegram = new BotApi($botToken);
    }

    public function handleUpdate(array $update): void
    {
        if (isset($update['message'])) {
            $chatId = $update['message']['chat']['id'];
            $text = $update['message']['text'] ?? '';

            $this->sendMessage($chatId, $text);
        }
    }
    /**
     * Обёртка sendMessage для Telegram API
     *
     * @param int|string $chatId
     * @param string $text
     * @param string|null $parseMode
     * @param bool $disablePreview
     * @param int|null $replyToMessageId
     * @param mixed|null $replyMarkup
     * @param bool $disableNotification
     * @param int|null $messageThreadId
     * @param bool|null $protectContent
     * @param bool|null $allowSendingWithoutReply
     *
     * @return Message
     * @throws \TelegramBot\Api\Exception
     * @throws \TelegramBot\Api\InvalidArgumentException
     */
    public function sendMessage(
        $chatId,
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
            $allowSendingWithoutReply,
        );
    }

    public function getMe()
    {
        return $this->telegram->getMe();
    }

    public function getUpdates(): array
    {
        return $this->telegram->getUpdates();
    }

    public function sendTestInlineKeyboard(int $chatId): Message
    {
        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => 'Личный кабинет', 'callback_data' => 'personal_account'],
                ['text' => 'Заказы', 'callback_data' => 'personal_orders']
            ],
            [
                ['text' => 'Помощь', 'callback_data' => 'personal_help']
            ]
        ]);

        $text = "Добро пожаловать, {$dto->getFirstName()}!\nВыберите действие:";



        return $this->sendMessage($chatId, 'Choose_bottom', null, false, null, $keyboard);
    }
}
