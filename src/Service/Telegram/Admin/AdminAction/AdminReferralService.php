<?php

namespace App\Service\Telegram\Admin\AdminAction;

use App\Repository\TelegramUserRepository;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Enum\TelegramParseMode;
use App\Service\Telegram\TelegramMessageCache;
use App\Service\TelegramBotService;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class AdminReferralService
{
    public function __construct(
        private TelegramUserRepository $telegramUserRepository,
        private TelegramBotService $telegramBotService,
        private TelegramMessageCache $cache,
    ) {
    }

    public function handle(int $chatId, int $currentMessage = 0): void
    {
        $referrals = $this->getReferralStatistics();
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

        $message = $this->telegramBotService->sendMessage(
            $chatId,
            $text,
            TelegramParseMode::MARKDOWN,
            false,
            null,
            $keyboard
        );

        $this->cache->clear(TelegramCacheKey::STEP, $chatId, $currentMessage, $message->getMessageId());
    }

    private function getReferralStatistics(): array
    {
        return $this->telegramUserRepository->findReferralsStatistics();
    }
}
