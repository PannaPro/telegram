<?php

namespace App\Service\Telegram\Handler;

use App\Http\Dto\AbstractPayload;
use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Http\Dto\MessageTelegramPayload;

class PayloadHandler
{
    public function __construct(
        private MessageHandler $messageHandler,
        private CallbackQueryHandler $callbackQueryHandler,
    ) {
    }

    public function handlePayload(AbstractPayload $dto): void
    {
        match (true) {
            $dto instanceof MessageTelegramPayload => $this->messageHandler->makeAction($dto),
            $dto instanceof CallbackQueryTelegramPayload => $this->callbackQueryHandler->makeAction($dto),
            default => null,
        };
    }
}
