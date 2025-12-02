<?php

namespace App\Controller;

use App\Http\Dto\AbstractPayload;
use App\Service\Telegram\Handler\PayloadHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;


final class TelegramController extends AbstractController
{
    public function __construct(
        private PayloadHandler $payloadHandler
    ) {
    }

    #[Route('telegram/test-webhook', name: 'telegram_test-webhook')]
    public function send(
        #[MapRequestPayload] AbstractPayload $payload,
    ): Response
    {
        $this->payloadHandler->handlePayload($payload);

        return new Response('Message processed', Response::HTTP_OK);
    }
}
