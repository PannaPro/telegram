<?php

namespace App\Service\Telegram\Handler;

use App\Http\Dto\AbstractPayload;
use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Service\Telegram\Action\AvatarSetService;
use App\Service\Telegram\Action\ParticipateService;

class CallbackQueryHandler
{
    public function __construct(
        private ParticipateService $participateService,
        private AvatarSetService $avatarSetService,
    ) {
    }

    /**
     * @param CallbackQueryTelegramPayload $dto
     */
    public function makeAction(AbstractPayload $dto): void
    {
        $data = $dto->getCallbackData();

        switch ($data) {
            case 'participate':
                $this->participateService->handleCallbackQuery($dto);
                break;
            case 'avatarSet':
                $this->avatarSetService->handleCallbackQuery($dto);
                break;
            default:
//                $this->unknownCommand($dto);
        }
    }
}
