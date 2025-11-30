<?php

namespace App\Service\Telegram\Handler;

use App\Http\Dto\AbstractPayload;
use App\Http\Dto\MessageTelegramPayload;
use App\Http\Dto\MyChatMemberPayload;

class PayloadHandler
{
    public function __construct(
        private MessageHandler $messageHandler,
        private MyChatMemberHandler $myChatMemberHandler,
    )
    {
    }

    public function handlePayload(AbstractPayload $dto): void
    {
        match (true) {
            $dto instanceof MessageTelegramPayload => $this->messageHandler->makeAction($dto),
            $dto instanceof MyChatMemberPayload => $this->myChatMemberHandler->makeAction($dto),
            // $dto instanceof CallbackPayload => ...
            default => null
        };
    }
}
