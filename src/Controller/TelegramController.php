<?php

namespace App\Controller;

use App\Http\Dto\AbstractPayload;
use App\Security\SecurityTelegramUserService;
use App\Service\Telegram\Handler\PayloadHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class TelegramController extends AbstractController
{
    public function __construct(
        private PayloadHandler $payloadHandler,
        private SecurityTelegramUserService $security,
    ) {
    }

    #[Route('/test-webhook', name: 'telegram_test-webhook', methods: ['POST'])]
    public function send(
        #[MapRequestPayload] AbstractPayload $payload,
    ): Response
    {
        // TODO подключить логи всех входящих вебхуков, разобраться, почему отправляет в канал сообщение
        $this->security->setCurrentTelegramUser($payload);
        $this->payloadHandler->handlePayload($payload);

        return new Response('Message processed', Response::HTTP_OK);
    }
}
