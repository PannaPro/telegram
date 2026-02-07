<?php

namespace App\RequestHandler;

use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\TelegramMessageCache;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class TelegramUpdateGuard implements EventSubscriberInterface
{
    public function __construct(
        private readonly TelegramMessageCache $cache,
        private readonly LoggerInterface $webhookLogger,
        private readonly LoggerInterface $webhookPayloadLogger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 20],
            KernelEvents::RESPONSE => ['onResponse', 0],
        ];
    }

    public function onRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->isMethod('POST')) {
            return;
        }

        if (!in_array($request->getRequestUri(), ['/webhook', '/test-webhook'], true)) {
            return;
        }

        $payload = json_decode($request->getContent(), true);

        if (!$payload || !isset($payload['update_id'])) {
            $this->webhookLogger->debug("Got invalid message:", $payload);
            return;
        }

        $updateId = $payload['update_id'];
        $this->webhookLogger->debug("Update_id $updateId has been received");
        $this->webhookPayloadLogger->debug($updateId, $payload);

        $lastUpdate = $this->cache->get(TelegramCacheKey::LAST_UPDATE, $updateId);
        if ($lastUpdate !== false && $updateId <= (int)$lastUpdate) {
            $this->webhookLogger->debug("$updateId Duplicate update ignored, current update: $lastUpdate");
//            $event->setResponse(new Response('Duplicate update ignored', Response::HTTP_OK));
            return;
        }

        $event->getRequest()->attributes->set('telegram_update_id', $updateId);
    }

    public function onResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();

        $updateId = $request->attributes->get('telegram_update_id');

        if ($updateId) {
            $this->cache->setEx(TelegramCacheKey::LAST_UPDATE, $updateId, TelegramCacheKey::TTL_5_MINUTES, $updateId);
            $this->webhookLogger->debug("Update_id $updateId stored in Redis");
            $this->webhookLogger->debug("Update_id $updateId response has been sent");
        }
    }
}
