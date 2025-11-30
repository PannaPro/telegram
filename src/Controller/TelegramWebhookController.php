<?php

namespace App\Controller;

use App\Entity\TelegramUser;
use App\Service\TelegramBotService;
use Doctrine\ORM\EntityManagerInterface;
use http\Client\Request;
use http\Client\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/telegram/webhook', name: 'telegram_webhook', methods: ['POST'])]
class TelegramWebhookController
{
    public function __invoke(Request $request, TelegramBotService $botService, EntityManagerInterface $em): Response
    {
        if (isset($data['message'])) {
            $message = $data['message'];
            $chat = $message['chat'];
            $chatId = $chat['id'];

            $userRepo = $em->getRepository(TelegramUser::class);
            $user = $userRepo->findOneBy(['chatId' => $chatId]);

            if (!$user) {
                $user = new TelegramUser(
                    $chatId,
                    $chat['username'] ?? null,
                    $chat['first_name'] ?? null,
                    $chat['last_name'] ?? null
                );
                $em->persist($user);
                $em->flush();
            }

            if (($message['text'] ?? '') === '/start') {
                $botService->sendMessage($chatId, "Hi, {$chat['first_name']}");
            }
        }

        return new Response('ok');
    }
}
