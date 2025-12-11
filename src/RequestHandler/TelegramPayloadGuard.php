<?php

namespace App\RequestHandler;

use App\Service\Telegram\Enum\TelegramDefaultValue;
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
            KernelEvents::REQUEST => ['onRequest', 10],
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
        $updateId = $payload['update_id'];

        $type = $this->extractAvailablePayloadType($payload);
        if ($type === TelegramDefaultValue::UNKNOWN) {
            $this->logger->debug("$updateId Unsupported payload type");
            $event->setResponse(new Response("The bot doesn't yet support the transmitted message type", Response::HTTP_OK));
        }
    }

    private function extractAvailablePayloadType(array $payload): string
    {
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

        return 'unknown';
    }
}
