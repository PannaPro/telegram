<?php

namespace App\Service\Telegram\Handler;

use App\Http\Dto\AbstractPayload;
use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Service\Telegram\Action\ParticipateService;

class CallbackQueryHandler
{
    public function __construct(
        private ParticipateService $participateService,
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
                // TODO отправлять 2 сообщение если не указан юзернейм
                // TODO принять участие - если нет юзернейм не выдавать картинку, установите его
                // генерировать картинку с внутренним айди юзера если есть юзернейм
                $this->participateService->handleCallbackQuery($dto);
                break;
            default:
//                $this->unknownCommand($dto);
        }
    }
}
