<?php

namespace App\Service\Telegram\Admin\Handler;

use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Service\Telegram\Admin\AdminAction\AdminMenuService;
use App\Service\Telegram\Admin\AdminAction\AdminReferralService;
use App\Service\Telegram\Admin\AdminAction\ReferralSearch\ReferralSearchService;
use App\Service\Telegram\Admin\AdminAction\ReferralSearch\ReferralSearchStorage;
use App\Service\Telegram\UnknownCommandService;
use function Symfony\Component\String\b;

class AdminCallbackQueryHandler
{
    public function __construct(
        private UnknownCommandService $unknownCommandService,
        private ReferralSearchService $referralSearchService,
        private AdminReferralService $adminReferralService,
        private ReferralSearchStorage $referralSearchStorage,
        private AdminMenuService $adminMenuService,
    )
    {
    }

    public function makeAction(CallbackQueryTelegramPayload $payload): void
    {
        $data = $payload->getCallbackData();
        $chatId = $payload->getChatId();
        $messageId = $payload->getMessageId();

        switch ($data) {
            case 'participantReferral':
                $this->referralSearchService->prepareToSearch($chatId, 'participantReferral', $messageId);
                break;
            case 'back_to_referral_menu':
                $this->referralSearchStorage->unsetReferralSearchWaiting($chatId);
                $this->adminMenuService->handle($chatId, $messageId);
                $this->adminReferralService->handle($chatId, $messageId);
                break;
            case 'back_to_admin_menu':
                $this->referralSearchStorage->unsetReferralSearchWaiting($chatId);
                $this->adminMenuService->handle($chatId, $messageId);
                break;
            case 'allPeriod':
                $this->referralSearchService->prepareDataToSearch($chatId, 'allPeriod', $messageId);
                break;
            default:
                $this->unknownCommandService->handleCallbackQuery($chatId, $data);
        }
    }
}
