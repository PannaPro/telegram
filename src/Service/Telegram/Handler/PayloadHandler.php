<?php

namespace App\Service\Telegram\Handler;

use App\Http\Dto\AbstractPayload;
use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Http\Dto\MessageTelegramPayload;
use App\Service\Telegram\Action\UnknownCommandService;
use App\Service\Telegram\Subscription\SubscriptionService;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;

#[WithMonologChannel('webhook')]
class PayloadHandler
{
    public function __construct(
        private MessageHandler       $messageHandler,
        private CallbackQueryHandler $callbackQueryHandler,
        private LoggerInterface      $logger,
        private UnknownCommandService $unknownCommandService,
        private SubscriptionService $subscriptionService,
    ) {
    }

    public function handlePayload(AbstractPayload $dto): void
    {
        $this->logger->debug('Payload data: ' . $dto->update_id);
        $chatId = $dto->getChatId();

        if (!$this->subscriptionService->check($chatId)) {
            $this->subscriptionService->needSubscription($chatId);

            return;
        }

        match (true) {
            $dto instanceof MessageTelegramPayload => $this->messageHandler->makeAction($dto),
            $dto instanceof CallbackQueryTelegramPayload => $this->callbackQueryHandler->makeAction($dto),
            default => $this->unknownCommandService->handle(),
        };
    }
}
