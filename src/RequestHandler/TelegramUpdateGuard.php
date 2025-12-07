<?php

namespace App\RequestHandler;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Redis;

#[WithMonologChannel('webhook_payload')]
class TelegramUpdateGuard implements EventSubscriberInterface
{
    public function __construct(
        private readonly Redis $redis,
        private readonly LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 10],
            KernelEvents::RESPONSE => ['onResponse', 0],
        ];
    }

    public function onRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->isMethod('POST')) {
            return;
        }

        // TODO temporary
        if (!in_array($request->getRequestUri(), ['/webhook', '/test-webhooks'], true)) {
            return;
        }

        $payload = json_decode($request->getContent(), true);
        $this->logger->debug($payload['update_id']);
        if (!$payload || !isset($payload['update_id'])) {
            return;
        }

        $updateId = $payload['update_id'];
        $chatId = $this->extractChatId($payload);
        if ($chatId === 0) {
            $event->setResponse(new Response('Invalid request', 404));
        }

        $key = "last_update:$chatId";
        $lastUpdate = $this->redis->get($key);

        if ($lastUpdate !== false && $updateId < (int)$lastUpdate) {
            $this->logger->debug($updateId . "Duplicate update ignored for $lastUpdate");
            $event->setResponse(new Response('Duplicate update ignored', 200));
            return;
        }

        $event->getRequest()->attributes->set('telegram_update_id', $updateId);
        $event->getRequest()->attributes->set('telegram_chat_id', $chatId);

//        $this->redis->setEx($key, 3600, $updateId);
    }

    private function extractChatId(array $payload): int
    {
        return $payload['message']['chat']['id'] ??
            $payload['edited_message']['chat']['id'] ??
            $payload['callback_query']['message']['chat']['id'] ??
            $payload['channel_post']['chat']['id'] ??
            0;
    }

    public function onResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();

        $updateId = $request->attributes->get('telegram_update_id');
        $chatId = $request->attributes->get('telegram_chat_id');

        if ($updateId && $chatId) {
            $key = "last_update:$chatId";
            $this->redis->setEx($key, 3600, $updateId);
            $this->logger->debug("Update $updateId stored in Redis for chat $chatId");
        }
    }
}
