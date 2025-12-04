<?php

namespace App\Service\Telegram\Handler;

use App\Http\Dto\AbstractPayload;
use App\Http\Dto\MessageTelegramPayload;
use App\Service\Telegram\Action\InfoService;
use App\Service\Telegram\Action\ParticipateService;
use App\Service\Telegram\Action\StartService;

class MessageHandler
{
    public function __construct(
        private StartService $startService,
        private InfoService $infoService,
        private ParticipateService $participateService,
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
                $this->startService->handle($dto);
                break;
            case '💡 Инфо':
                $this->infoService->handle($dto);
                break;
            case '🎲 Участвовать':
                $this->participateService->handle($dto);
                break;
            // Добавляй свои команды
            default:
//                $this->unknownCommand($dto);
        }
    }
}
