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
            MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => '🔍 Поиск по рефералам', 'callback_data' => 'search_top_referral'],
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

    public function sendReferralSearchResult(int $chatId, array $result, string $textHeader): int
    {
        $text = <<<MARKDOWN
            $textHeader

            *Топ найденных игроков:*
            MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => 'Наградить участников', 'callback_data' => 'reward_participant'],
            ],
            [
                ['text' => 'Скачать результаты', 'callback_data' => 'download_search_result'],
            ],
            [
                ['text' => '🔍 Новый поиск', 'callback_data' => 'search_top_referral'],
            ],
            [
                ['text' => 'Главное меню', 'callback_data' => 'back_to_admin_menu'],
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
