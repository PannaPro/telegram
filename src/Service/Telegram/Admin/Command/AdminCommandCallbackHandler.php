<?php

namespace App\Service\Telegram\Admin\Command;

use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Service\Telegram\Admin\Service\AdminReferralSearchService;
use App\Service\Telegram\Admin\Service\AdminReferralService;
use App\Service\Telegram\Common\UnknownCommandService;

class AdminCommandCallbackHandler
{
    public function __construct(
        private UnknownCommandService $unknownCommandService,
        private AdminReferralService $adminReferralService,
        private AdminReferralSearchService $adminReferralSearchService,
    ) {
    }

    public function handleCommand(CallbackQueryTelegramPayload $payload): void
    {
        $data = $payload->getCallbackData();
        $chatId = $payload->getChatId();
        $callbackId = $payload->getCallbackQueryId();

        switch ($data) {
            case 'show_top_referral':
                $this->adminReferralService->makeTopReferralAction($chatId, $callbackId);
                break;
            case 'participant_referral':
                $this->adminReferralSearchService->makeParticipantReferralAction($chatId, $callbackId);
                break;
            default:
                $this->unknownCommandService->handleCallbackQuery($chatId, $data);
        }
    }
}
