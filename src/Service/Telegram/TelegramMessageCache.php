<?php

namespace App\Service\Telegram;

use App\Service\TelegramBotService;
use Redis;

final class TelegramMessageCache
{
    public function __construct(
        private Redis $redis,
        private TelegramBotService $telegramBotService,
    ) {
    }

    private function getKey(string $keyType, int $keyValue): string
    {
        return "$keyType:$keyValue";
    }

    public function getMessages(string $type, int $chatId): array
    {
        $json = $this->redis->get($this->getKey($type, $chatId));

        return $json ? json_decode($json, true) : [];
    }

    /**
     * Удаляет все сообщения, у которых delete = true
     */
    public function cleanup(string $type, int $chatId): void
    {
        $messages = $this->getMessages($type, $chatId);

        foreach ($messages as $msg) {
            if (!empty($msg['delete'])) {
                $this->telegramBotService->deleteMessage($chatId, $msg['id']);
            }
        }

        unset($messages);
    }

    public function clear(string $type, int $chatId, int $currentMessage, int $newMessage): void
    {
        $this->cleanup($type, $chatId);
        $this->telegramBotService->deleteMessage($chatId, $currentMessage);

        $this->set($type, $chatId, [
            'id' => $newMessage,
            'type' => $type,
            'delete' => true
        ]);
    }

    /**
     * Сохраняет одно новое сообщение, заменяя предыдущие.
     */
    public function set(string $type, int $chatId, array $value): void
    {
        $this->redis->set(
            $this->getKey($type, $chatId),
            json_encode([$value])
        );
    }

    /**
     * Полный цикл: удалить старые → сохранить новое
     */
    public function saveAndCleanup(string $type, int $chatId, int $messageId, bool $delete = true): void
    {
        $this->cleanup($type, $chatId);

        $this->set($type, $chatId, [
            'id' => $messageId,
            'type' => $type,
            'delete' => $delete
        ]);
    }

    public function setEx(string $keyType, int $keyValue, int $ttl, mixed $value): void
    {
        $this->redis->setEx($this->getKey($keyType, $keyValue), $ttl, $value);
    }

    public function get(string $keyType, int $keyValue): mixed
    {
        return $this->redis->get($this->getKey($keyType, $keyValue));
    }

    public function delete(string $keyType, int $keyValue): int
    {
        return $this->redis->delete($this->getKey($keyType, $keyValue));
    }
}
