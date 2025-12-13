<?php

namespace App\Service\Telegram\Router;

use App\Http\Dto\AbstractPayload;
use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Http\Dto\MessageTelegramPayload;
use App\Service\Telegram\Admin\Command\AdminCommandCallbackHandler;
use App\Service\Telegram\Admin\Command\AdminCommandMessageHandler;
use App\Service\Telegram\Context\ContextStorage;

class AdminPayloadRouter
{
    public function __construct(
        private ContextStorage $contextStorage,
        private AdminContextRouter $adminContextHandler,
        private AdminCommandMessageHandler $messageHandler,
        private AdminCommandCallbackHandler $callbackHandler,
    ) {
    }

    public function route(AbstractPayload $payload): void
    {
        $chatId = $payload->getChatId();

        if ($this->contextStorage->hasContext($chatId)) {
            $context = $this->contextStorage->getContext($chatId);
            if ($context) {
                $this->adminContextHandler->route($payload, $context);
                return;
            }

            $this->contextStorage->unsetContext($chatId);
        }

        match (true) {
            $payload instanceof MessageTelegramPayload => $this->messageHandler->handleCommand($payload),
            $payload instanceof CallbackQueryTelegramPayload => $this->callbackHandler->handleCommand($payload),
            default => null,
        };
    }
}
