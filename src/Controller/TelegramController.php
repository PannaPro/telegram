<?php

namespace App\Controller;

use App\Service\TelegramBotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


final class TelegramController extends AbstractController
{
    public function __construct(
        private TelegramBotService $bot,
    ) {
    }

    #[Route('telegram/test-webhook', name: 'telegram_test-webhook')]
    public function send(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        return new Response('Message sent');
    }
}
