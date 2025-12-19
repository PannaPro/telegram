<?php

namespace App\Service\Telegram\Admin\Service;

use App\Repository\ReferralSearchRepository;
use App\Repository\TelegramUserRepository;
use App\Service\Telegram\Context\Dto\ReferralSearchContext;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Handler\AnswerCallbackQueryTrait;
use App\Service\Telegram\Message\AdminReferralMessage;
use App\Service\Telegram\TelegramMessageCache;
use App\Service\TelegramBotService;
use CURLFile;

readonly class AdminReferralService
{
    use AnswerCallbackQueryTrait;
    public function __construct(
        private TelegramUserRepository $telegramUserRepository,
        private TelegramMessageCache $cache,
        private AdminReferralMessage $referralMessage,
        private ReferralSearchRepository $referralSearchRepository,
        private TelegramBotService $bot,
    )
    {
    }

    public function makeAction(int $chatId, int $currentMessage = 0): void
    {
        $referrals = $this->telegramUserRepository->findReferralsStatistics();
        $messageId = $this->referralMessage->sendMessage($chatId, $referrals);

        $this->cache->deleteMessage($chatId, $currentMessage);
        $this->cache->replaceMessage(TelegramCacheKey::STEP, $chatId, $messageId);
    }

    public function search(ReferralSearchContext $context): void
    {
        $chatId = $context->getChatId();
        $textHeader = $context->getTextType();

        $result = $this->referralSearchRepository->findReferralBySearch($context);
        $count = count($result);

        if ($count > 0) {
            $text = $this->buildReferralResultText($result, $count, $textHeader);
            $messageId = $this->referralMessage->sendReferralSearchResult($chatId, $text);
        } else {
            $messageId = $this->referralMessage->sendReferralSearchEmptyResult($chatId, $textHeader);
        }

        $this->cache->saveAndClean(TelegramCacheKey::CONTEXT_MESSAGE, $chatId, $messageId);
    }

    public function buildReferralResultText(array $result, int $count, string $textHeader): string
    {
        $lines = [];
        $limit = 5;

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

    public function downloadResult(int $chatId, int $callbackId, ReferralSearchContext $context): void
    {
        $this->answerCallbackQuery($callbackId);

        $result = $this->referralSearchRepository->findReferralBySearch($context);
        $filePath = $this->generateFile($context->getTextType(), $result);

        $this->referralMessage->sendResultFile($chatId, new CURLFile($filePath));
    }

    private function generateFile(string $title, array $data): string
    {
        $date = (new \DateTime())->format('d-m-Y H:i');

        $fileName = 'referrals_' . date('Ymd_His') . '.csv';
        $filePath = sys_get_temp_dir() . '/' . $fileName;

        $handle = fopen($filePath, 'w');

        fputcsv($handle, ["$title (запрос $date)"]);
        fputcsv($handle, []);
        fputcsv($handle, ['Chat_ID', 'Username', 'Кол-во рефералов']);

        foreach ($data as $item) {
            fputcsv($handle, [
                $item['chatId'],
                "@{$item['username']}",
                $item['referralCount'],
            ]);
        }

        fclose($handle);

        return $filePath;
    }
}
