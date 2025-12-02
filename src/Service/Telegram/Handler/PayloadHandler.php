<?php

namespace App\Service\Telegram\Handler;

use App\Http\Dto\AbstractPayload;
use App\Http\Dto\MessageTelegramPayload;
use App\Http\Dto\MyChatMemberPayload;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;

#[WithMonologChannel('webhook')]
class PayloadHandler
{
    public function __construct(
        private MessageHandler $messageHandler,
        private MyChatMemberHandler $myChatMemberHandler,
        private LoggerInterface $logger,
    )
    {
    }

    public function handlePayload(AbstractPayload $dto): void
    {
        $dtoArray = method_exists($dto, 'toArray') ? $dto->toArray() : get_object_vars($dto);

        // Логируем массив
        $this->logger->debug('Payload data: ' . json_encode($dtoArray, JSON_PRETTY_PRINT));

        match (true) {
            $dto instanceof MessageTelegramPayload => $this->messageHandler->makeAction($dto),
            $dto instanceof MyChatMemberPayload => $this->myChatMemberHandler->makeAction($dto),
            // $dto instanceof CallbackPayload => ...
            default => null
        };
    }
}
