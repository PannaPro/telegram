<?php

namespace App\Service\Telegram\Action;

use App\Http\Dto\MessageTelegramPayload;
use App\Service\TelegramBotService;
use CURLFile;
use Redis;

class InfoService
{
    public function __construct(
        private TelegramBotService $telegramBotService,
        private Redis $redis,
    ) {
    }

    public function handle(MessageTelegramPayload $dto): void
    {
        $chatId = $dto->getChatId();
        $type = 'info';
        $key = "/info:$chatId";

        $messagesJson = $this->redis->get($key);
        $messages = $messagesJson ? json_decode($messagesJson, true) : [];
        foreach ($messages as $msg) {
            if (isset($msg['delete']) && $msg['delete']) {
                try {
                    $this->telegramBotService->deleteMessage($chatId, $msg['id']);
                } catch (\Exception) {

                }
            }
        }
        unset($messages);

        $photoPath = '/app/public/image/pipe.jpg';

        $message = $this->telegramBotService->sendPhoto(
            $chatId,
            new CURLFile($photoPath),
            "Инфо",
            null,
            null,
            false,
            'Markdown'
        );

        $messages = [[
            'id' => $message->getMessageId(),
            'type' => $type,
            'delete' => true
        ]];

        $this->redis->set($key, json_encode($messages));
    }
}
