<?php

namespace App\RequestHandler;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;

#[WithMonologChannel('webhook_payload')]
class TelegramPayloadGuard implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 1],
        ];
    }

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
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
            $event->setResponse(new Response('Invalid payload', Response::HTTP_BAD_REQUEST));
        }

        $type = $this->extractAvailablePayloadType($payload);
        if ($type === 'unknown') {
            $event->setResponse(new Response("The bot doesn't yet support the transmitted message type", Response::HTTP_ACCEPTED));
        }
    }

    private function extractAvailablePayloadType(array $payload): string
    {
        $update = $payload['update_id'];
        $this->logger->debug($update, $payload);

        $supportedTypes = [
            'message' => true,
            'my_chat_member' => true,
            'callback_query' => true
        ];

        foreach ($supportedTypes as $type => $supported) {
            if (isset($payload[$type])) {
                return $type;
            }
        }

        $this->logger->debug("$update -unsupported payload type");

        return 'unknown';
    }
}
