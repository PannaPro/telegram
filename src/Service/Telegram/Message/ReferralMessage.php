<?php

namespace App\Service\Telegram\Message;

use App\Service\TelegramBotMessaging\BotMessengerInterface;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class ReferralMessage
{
    public function __construct(
        private BotMessengerInterface $bot,
    ) {
    }

    public function sendMessage(int $chatId, string $referralCode, int $referralCount): int
    {
        $text = rawurlencode("Привет! Нашел крутого бота где проводятся игры, а призы реальные NFT!");
        $botLink = "https://t.me/share?url=https://t.me/PAKETAGAME_bot?start=$referralCode&text=$text";

        $text = <<<MARKDOWN
        👥 *Ваши рефералы:*
        Всего приглашенных: *{$referralCount}*
        MARKDOWN;

        $inlineKeyboard = new InlineKeyboardMarkup([
            [
                ['text' => 'Пригласить', 'url' => $botLink],
            ]
        ]);

        $message = $this->bot->sendMessage(
            $chatId,
            $text,
            'Markdown',
            false,
            null,
            $inlineKeyboard
        );

        return $message->getMessageId();
    }
}
