<?php

namespace App\Controller;

use App\Service\TelegramBotService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/webhook', name: 'telegram_webhook', methods: ['POST'])]
class TelegramWebhookController
{
    #[Route('/webhook', name: 'telegram_webhook', methods: ['POST'])]
    public function __invoke(Request $request, TelegramBotService $botService, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);

        // Логируем входящую нагрузку (если нужно)
        // $botService->log($data);

        // Обработка апдейта
        $botService->handleUpdate($data);

        return new Response('ok');
    }
}
