<?php

namespace App\Controller;

use App\Http\Dto\AbstractPayload;
use App\Service\Telegram\Handler\PayloadHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

class TelegramWebhookController extends AbstractController
{
    public function __construct(
        private PayloadHandler $payloadHandler,
    ) {
    }

    #[Route('/webhook', name: 'telegram_webhook', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] AbstractPayload $payload,
    ): Response
    {
        $this->payloadHandler->handlePayload($payload);

        return new Response('Message processed', Response::HTTP_OK);
    }
}
