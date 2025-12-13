<?php

namespace App\Service\Telegram\Admin\Command;

use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Service\Telegram\Common\UnknownCommandService;

class AdminCommandCallbackHandler
{
    public function __construct(
        private UnknownCommandService $unknownCommandService,
//        private ReferralSearchService $referralSearchService,
//        private AdminReferralService $adminReferralService,
//        private AdminMenuService $adminMenuService,
    ) {
    }

    public function handleCommand(CallbackQueryTelegramPayload $payload): void
    {
        $data = $payload->getCallbackData();
        $chatId = $payload->getChatId();
        $messageId = $payload->getMessageId();

        switch ($data) {
//            case 'participantReferral':
//                $this->referralSearchService->prepareToSearch($chatId, 'participantReferral', $messageId);
//                break;
//            case 'back_to_referral_menu':
////                $this->referralSearchStorage->unsetReferralSearchWaiting($chatId);
//                $this->adminMenuService->handle($chatId, $messageId);
//                $this->adminReferralService->handle($chatId, $messageId);
//                break;
//            case 'back_to_admin_menu':
////                $this->referralSearchStorage->unsetReferralSearchWaiting($chatId);
//                $this->adminMenuService->handle($chatId, $messageId);
//                break;
//            case 'allPeriod':
//                $this->referralSearchService->prepareDataToSearch($chatId, 'allPeriod', $messageId);
//                break;
            default:
                $this->unknownCommandService->handleCallbackQuery($chatId, $data);
        }
    }
}
