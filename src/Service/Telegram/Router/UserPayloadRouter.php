<?php

namespace App\Service\Telegram\Router;

use App\Http\Dto\AbstractPayload;
use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Http\Dto\MessageTelegramPayload;
use App\Service\Telegram\Context\ContextStorage;
use App\Service\Telegram\User\Command\UserCommandCallbackQueryHandler;
use App\Service\Telegram\User\Command\UserCommandMessageHandler;

class UserPayloadRouter
{
    public function __construct(
        private ContextStorage $contextStorage,
        private UserCommandCallbackQueryHandler $callbackQueryHandler,
        private UserCommandMessageHandler $messageHandler,
        private UserContextRouter $userContextRouter,
    ) {
    }

    public function route(AbstractPayload $payload): void
    {
        $chatId = $payload->getChatId();

        if ($this->contextStorage->hasContext($chatId)) {
            $context = $this->contextStorage->getContext($chatId);

            if ($context) {
                $this->userContextRouter->route($payload, $context);
                return;
            }

            $this->contextStorage->unsetContext($chatId);
        }

        match (true) {
            $payload instanceof MessageTelegramPayload => $this->messageHandler->handleCommand($payload),
            $payload instanceof CallbackQueryTelegramPayload => $this->callbackQueryHandler->handleCommand($payload),
            default => null,
        };
    }
}
