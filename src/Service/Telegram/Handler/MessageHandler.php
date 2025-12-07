<?php

namespace App\Service\Telegram\Handler;

use App\Http\Dto\MessageTelegramPayload;
use App\Service\Telegram\Action\GameService;
use App\Service\Telegram\Action\InfoService;
use App\Service\Telegram\Action\ParticipateService;
use App\Service\Telegram\Action\StartService;
use App\Service\Telegram\Action\UnknownCommandService;
use App\Service\Telegram\Subscription\SubscriptionService;

class MessageHandler
{
    public function __construct(
        private StartService $startService,
        private InfoService $infoService,
        private ParticipateService $participateService,
        private GameService $gameService,
        private UnknownCommandService $unknownCommandService,
        private SubscriptionService $subscriptionService,
    ) {
    }

    public function makeAction(MessageTelegramPayload $dto): void
    {
        $text = $dto->getText();
        $messageId = $dto->getMessageId();

        $chatId = $dto->getChatId();
        if (!$this->subscriptionService->check($chatId)) {
            $this->subscriptionService->needSubscription($chatId);

            return;
        }

        switch ($text) {
            case '/start':
            case 'Вернуться в меню':
                $this->startService->handle($messageId);
                break;
            case '💡 Инфо':
                $this->infoService->handle($messageId);
                break;
            case '🎲 Игры':
                $this->gameService->handle($messageId);
                break;
            case '👕 Получить номер':
                $this->participateService->participateMessage($messageId);
                break;
            default:
                $this->unknownCommandService->handle($dto);
        }
    }
}
