<?php

namespace App\Service\Telegram\Handler;

use App\Http\Dto\AbstractPayload;
use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Http\Dto\MessageTelegramPayload;
use App\Http\Dto\MyChatMemberPayload;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;

#[WithMonologChannel('webhook')]
class PayloadHandler
{
    public function __construct(
        private MessageHandler       $messageHandler,
        private MyChatMemberHandler  $myChatMemberHandler,
        private CallbackQueryHandler $callbackQueryHandler,
        private LoggerInterface      $logger,
    )
    {
    }

    public function handlePayload(AbstractPayload $dto): void
    {
        $this->logger->debug('Payload data: ' . $dto->update_id);

        match (true) {
            $dto instanceof MessageTelegramPayload => $this->messageHandler->makeAction($dto),
            $dto instanceof MyChatMemberPayload => $this->myChatMemberHandler->makeAction($dto),
            $dto instanceof CallbackQueryTelegramPayload => $this->callbackQueryHandler->makeAction($dto),
            default => null
        };
    }
}
