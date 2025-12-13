<?php

namespace App\Service\Telegram\Message;

use App\Service\Telegram\Enum\TelegramParseMode;
use App\Service\TelegramBotService;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class AdminReferralMessage
{
    public function __construct(
        private TelegramBotService $bot,
    ) {
    }

    public function sendMessage(int $chatId, array $referrals): int
    {
        $totalUsers = $referrals['all'];
        $total = $referrals['total'];
        $today = $referrals['today'];
        $week = $referrals['week'];

        $text = <<<MARKDOWN
            *Число участников в боте:* $totalUsers

            *Общая статистика по рефералам*
            Всего: $total
            Сегодня: $today
            За неделю: $week

            Поиск по рефералам:
            MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => 'Рефералы', 'callback_data' => 'participantReferral'],
                ['text' => 'Рефералы +ЦД', 'callback_data' => 'targetActionReferral'],
            ]
        ]);

        $message = $this->bot->sendMessage(
            $chatId,
            $text,
            TelegramParseMode::MARKDOWN,
            false,
            null,
            $keyboard
        );

        return $message->getMessageId();
    }
}
