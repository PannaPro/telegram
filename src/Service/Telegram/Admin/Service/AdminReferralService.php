<?php

namespace App\Service\Telegram\Admin\Service;

use App\Repository\ReferralSearchRepository;
use App\Repository\TelegramUserRepository;
use App\Service\Telegram\Context\Dto\ReferralSearchContext;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Message\AdminReferralMessage;
use App\Service\Telegram\TelegramMessageCache;
use App\Service\TelegramBotService;

class AdminReferralService
{
    public function __construct(
        private TelegramUserRepository   $telegramUserRepository,
        private TelegramMessageCache     $cache,
        private AdminReferralMessage     $referralMessage,
        private ReferralSearchRepository $referralSearchRepository,
        private TelegramBotService $telegramBotService,
    )
    {
    }

    public function makeAction(int $chatId, int $currentMessage = 0): void
    {
        $referrals = $this->telegramUserRepository->findReferralsStatistics();
        $messageId = $this->referralMessage->sendMessage($chatId, $referrals);

        $this->cache->saveAndCleanup(TelegramCacheKey::STEP, $chatId, $currentMessage, $messageId);
    }

    public function search(ReferralSearchContext $context, int $contextMessage = 0): void
    {
        $chatId = $context->getChatId();
        $textHeader = $context->getTextType();

        $text = $this->getReferralByFilters($context, $textHeader);

        $messageId = $this->referralMessage->sendReferralSearchResult($chatId, $text);

//        $this->cache->saveAndCleanup(TelegramCacheKey::CONTEXT_MESSAGE, $chatId, $contextMessage, $messageId);
        $this->cache->saveAndClean(TelegramCacheKey::CONTEXT_MESSAGE, $chatId, $messageId);
    }

    public function getReferralByFilters(ReferralSearchContext $context, string $textHeader): string
    {
        $result = $this->referralSearchRepository->findReferralBySearch($context);

        $count = count($result);

        if ($count === 0) {
            return <<<MARKDOWN
            *Критерии поиска:*
            $textHeader

            Поиск не дал результатов
            MARKDOWN;
        }

        $lines = [];
        $limit = 2;

        foreach (array_slice($result, 0, $limit) as $item) {
            $lines[] = "[@{$item['username']}](https://t.me/{$item['username']}) — {$item['referralCount']} рефералов";
        }

        if ($count > $limit) {
            $lines[] = '...';
        }

        $linesText = implode(PHP_EOL, $lines);

        return <<<MARKDOWN
            *Критерии поиска:*
            $textHeader

            *Топ найденных игроков:*
            $linesText

            Всего найдено: $count
            MARKDOWN;
    }

    public function downloadResult(ReferralSearchContext $context)
    {
        $chatId = $context->getChatId();

        $result = $this->referralSearchRepository->findReferralBySearch($context);

        $title = $context->getTextType();
        $date = (new \DateTime())->format('d-m-Y H:i');

        $fileName = 'referrals_' . date('Ymd_His') . '.csv';
        $filePath = sys_get_temp_dir() . '/' . $fileName;

        $handle = fopen($filePath, 'w');

        // Заголовок файла
        fputcsv($handle, ["$title (запрос $date)"]);
        fputcsv($handle, []); // пустая строка

        // Заголовки колонок
        fputcsv($handle, ['ID', 'Пользователь', 'Юзернейм', 'Кол-во рефералов']);

        // Данные
        foreach ($result as $item) {
            fputcsv($handle, [
                $item['chatId'],
                $item['username'],
                "@{$item['username']}",
                $item['referralCount'],
            ]);
        }

        fclose($handle);

        $this->telegramBotService->sendFile($chatId, new \CURLFile($filePath), 'Результаты');

        return $filePath;
    }
}
