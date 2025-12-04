<?php

namespace App\RequestHandler;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Redis;

class TelegramUpdateGuard implements EventSubscriberInterface
{
    public function __construct(
        private readonly Redis $redis,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 10],
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
        if (!$payload || !isset($payload['update_id'])) {
            return;
        }

        $updateId = $payload['update_id'];
        $chatId = $this->extractChatId($payload);
        if ($chatId === null) {
            return;
        }

        $key = "last_update:$chatId";
        $lastUpdate = $this->redis->get($key);

        if ($lastUpdate !== false && $updateId <= (int)$lastUpdate) {
            $event->setResponse(new Response('Duplicate update ignored', 200));
            return;
        }

        $this->redis->setEx($key, 60, $updateId);
    }

    private function extractChatId(array $payload): ?int
    {
        return $payload['message']['chat']['id'] ??
            $payload['edited_message']['chat']['id'] ??
            $payload['callback_query']['message']['chat']['id'] ??
            $payload['channel_post']['chat']['id'] ??
            null;
    }
}
