<?php

namespace App\Service\Telegram\Handler;

use App\Http\Dto\MessageTelegramPayload;
use App\Service\Telegram\Action\GameService;
use App\Service\Telegram\Action\InfoService;
use App\Service\Telegram\Action\ParticipateService;
use App\Service\Telegram\Action\ReferralService;
use App\Service\Telegram\Action\StartService;
use App\Service\Telegram\Action\UnknownCommandService;
use App\Service\Telegram\Enum\TelegramDefaultValue;

class MessageHandler
{
    public function __construct(
        private StartService $startService,
        private InfoService $infoService,
        private ParticipateService $participateService,
        private GameService $gameService,
        private UnknownCommandService $unknownCommandService,
        private ReferralService $referralService,
    ) {
    }

    public function makeAction(MessageTelegramPayload $dto): void
    {
        $text = $dto->getText();
        $messageId = $dto->getMessageId();
        $chatId = $dto->getChatId();

        switch ($text) {
            case '/start':
            case 'Вернуться в меню':
                $this->startService->handle($messageId);
                break;
            case '💡 Инфо':
                $this->infoService->handle($messageId);
                break;
            case '🎲 Игры':
                $this->gameService->handle($messageId);
                break;
            case '👕 Получить номер':
                $this->participateService->participateMessage($messageId);
                break;
            case '👥 Рефералы':
                $this->referralService->handle($messageId);
                break;
            case str_contains($text, '/start'):
                $command = explode(' ', $text, 2);
                $param = $command[1] ?? TelegramDefaultValue::UNKNOWN;

                $this->referralService->addReferral($chatId, $param);
                $this->startService->handle($messageId);
                break;
            default:
                $this->unknownCommandService->handle($dto);
        }
    }
}
