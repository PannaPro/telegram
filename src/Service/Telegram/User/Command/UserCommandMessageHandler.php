<?php

namespace App\Service\Telegram\User\Command;

use App\Http\Dto\MessageTelegramPayload;
use App\Security\AdminSessionService;
use App\Service\Telegram\Common\UnknownCommandService;
use App\Service\Telegram\Enum\TelegramDefaultValue;
use App\Service\Telegram\User\Service\GameService;
use App\Service\Telegram\User\Service\InfoService;
use App\Service\Telegram\User\Service\ParticipateService;
use App\Service\Telegram\User\Service\ReferralService;
use App\Service\Telegram\User\Service\StartService;
use App\Service\Telegram\User\Service\SubscriptionService;

readonly class UserCommandMessageHandler
{
    public function __construct(
        private UnknownCommandService $unknownCommandService,
        private StartService $startService,
        private SubscriptionService $subscriptionService,
        private InfoService $infoService,
        private GameService $gameService,
        private ParticipateService $participateService,
        private ReferralService $referralService,
        private AdminSessionService $adminSession,
    ) {
    }

    public function handleCommand(MessageTelegramPayload $payload): void
    {
        $text = $payload->getText();
        $messageId = $payload->getMessageId();
        $chatId = $payload->getChatId();

        switch ($text) {
            case '/start':
            case '⬅ Вернуться в меню':
            if (!$this->subscriptionService->check($chatId)) {
                    $this->subscriptionService->needSubscription($chatId);
                    return;
                }

                $this->startService->makeAction($messageId);
                break;
            case '💡 Инфо':
                $this->infoService->makeAction($chatId, $messageId);
                break;
            case '🎲 Игры':
                $this->gameService->makeAction($chatId, $messageId);
                break;
            case '👕 Получить номер':
                $this->participateService->makeAction($messageId);
                break;
            case '👥 Рефералы':
                $this->referralService->makeAction($messageId);
                break;
            case '/tiptip':
                if ($this->adminSession->isAdmin()) {
                    $this->adminSession->activateWaitingPassword($chatId, $messageId);
                    break;
                }

                $this->unknownCommandService->makeAction($payload);
                break;
            case str_contains($text, '/start'):
                $this->addReferral($chatId, $text);
                if (!$this->subscriptionService->check($chatId)) {
                    $this->subscriptionService->needSubscription($chatId);
                    break;
                }

                $this->startService->makeAction($messageId);
                break;
            default:
                $this->unknownCommandService->makeAction($payload);
        }
    }

    private function addReferral(int $chatId, string $text): void
    {
        $referralLink = $this->parseReferralText($text);
        if ($referralLink === TelegramDefaultValue::UNKNOWN) {
            return;
        }

        $this->referralService->addReferral($chatId, $referralLink);
    }

    private function parseReferralText(string $text): string
    {
        $command = explode(' ', $text, 2);

        return $command[1] ?? TelegramDefaultValue::UNKNOWN;
    }
}
