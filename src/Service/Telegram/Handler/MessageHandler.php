<?php

namespace App\Service\Telegram\Handler;

use App\Http\Dto\AbstractPayload;
use App\Http\Dto\MessageTelegramPayload;
use App\Service\Telegram\Action\GameService;
use App\Service\Telegram\Action\InfoService;
use App\Service\Telegram\Action\ParticipateService;
use App\Service\Telegram\Action\StartService;
use App\Service\Telegram\Action\UnknownCommandService;

class MessageHandler
{
    public function __construct(
        private StartService $startService,
        private InfoService $infoService,
        private ParticipateService $participateService,
        private GameService $gameService,
        private UnknownCommandService $unknownCommandService,
    ) {
    }

    /**
     * @param MessageTelegramPayload $dto
     * @return void
     */
    public function makeAction(AbstractPayload $dto): void
    {
        $text = $dto->getText();

        switch ($text) {
            case '/start':
            case 'Вернуться в меню':
                $this->startService->handle();
                break;
            case '💡 Инфо':
                $this->infoService->handle();
                break;
            case '🎲 Игры':
                $this->gameService->handle();
                break;
            case '👕 Получить номер':
                $this->participateService->participateMessage();
                break;
            default:
                $this->unknownCommandService->handle();
        }
    }
}
