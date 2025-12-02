<?php

namespace App\Controller;

use App\Entity\TelegramUser;
use App\Service\TelegramBotService;
use Doctrine\ORM\EntityManagerInterface;
use http\Client\Request;
use http\Client\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/webhook', name: 'telegram_webhook', methods: ['POST'])]
class TelegramWebhookController
{
    public function __invoke(Request $request, TelegramBotService $botService, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);



        return new Response('ok');
    }
}
