<?php

namespace App\Service\Telegram\Admin\Command;

use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Service\Telegram\Admin\Service\AdminReferralSearchService;
use App\Service\Telegram\Common\CommonActionService;
use App\Service\Telegram\Common\UnknownCommandService;

class AdminCommandCallbackHandler
{
    public function __construct(
        private UnknownCommandService $unknownCommandService,
        private AdminReferralSearchService $adminReferralSearchService,
        private CommonActionService $commonActionService,
    ) {
    }

    public function handleCommand(CallbackQueryTelegramPayload $payload): void
    {
        $data = $payload->getCallbackData();
        $chatId = $payload->getChatId();
        $callbackId = $payload->getCallbackQueryId();
        $messageId = $payload->getMessageId();

        switch ($data) {
            case 'search_top_referral':
                $this->adminReferralSearchService->makeTopReferralAction($chatId, $callbackId);
                break;
            case 'close_pinned_message':
                $this->commonActionService->deletePinnedMessage($chatId, $callbackId, $messageId);
                break;
            default:
                $this->unknownCommandService->handleCallbackQuery($chatId, $data);
        }
    }
}
