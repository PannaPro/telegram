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
//
//    public function getMessages(string $type, int $chatId): array
//    {
//        $json = $this->redis->get($this->getKey($type, $chatId));
//
//        return $json ? json_decode($json, true) : [];
//    }
//
//    /**
//     * Удаляет все сообщения, у которых delete = true
//     */
//    public function cleanup(string $type, int $chatId): void
//    {
//        $messages = $this->get($type, $chatId);
//
//        $this->delete($messages);
//        foreach ($messages as $msg) {
//            if (!empty($msg['delete'])) {
//                $this->telegramBotService->deleteMessage($chatId, $msg['id']);
//            }
//        }
//
//        unset($messages);
//    }
//
//    public function clear(string $type, int $chatId, int $currentMessage, int $newMessage): void
//    {
//        $this->cleanup($type, $chatId);
//        $this->telegramBotService->deleteMessage($chatId, $currentMessage);
//    }
//
//    public function deleteMessage(string $type, int $chatId): void
//    {
//        $key = $this->getKey($type, $chatId);
//        $message = $this->redis->get($key);
//        if ($message) {
//            $this->telegramBotService->deleteMessage($chatId, (int)$message);
//        }
//
//        $this->redis->delete($key);
//    }
//
//    /**
//     * Полный цикл: удалить старые → сохранить новое
//     */
//    public function saveAndCleanup(string $type, int $chatId, int $messageId, bool $delete = true): void
//    {
//        $this->cleanup($type, $chatId);
//
//        $this->set($type, $chatId, [
//            'id' => $messageId,
//            'type' => $type,
//            'delete' => $delete
//        ]);
//    }

    private function setEx(string $key, int $ttl, mixed $value): void
    {
        $this->redis->setEx($key, $ttl, $value);
    }

    private function set(string $key, mixed $value): void
    {
        $this->redis->set($key, $value);
    }

    private function get(string $key): mixed
    {
        return $this->redis->get($key);
    }

    private function delete(string $key): int
    {
        return $this->redis->delete($key);
    }

    public function deleteMessage(string $keyType, int $chatId): void
    {
        $message = $this->getMessage($keyType, $chatId);
        if ($message) {
            $this->telegramBotService->deleteMessage($chatId, (int)$message);
        }

        $this->delete($this->getKey($keyType, $chatId));
    }

    public function getMessage(string $keyType, int $keyValue): mixed
    {
        return $this->get($this->getKey($keyType, $keyValue));
    }

    public function saveAndCleanup(string $keyType, int $chatId, int $currentMessage, int $newMessage = 0): void
    {
        $key = $this->getKey($keyType, $chatId);

        $oldMessage = $this->get($key);
        if ($oldMessage) {
            $this->telegramBotService->deleteMessage($chatId, (int)$oldMessage);
        }

        if ($currentMessage !== 0) {
            $this->telegramBotService->deleteMessage($chatId, $currentMessage);
        }

        $this->set($key, $newMessage);
    }

    public function saveAndClean(string $keyType, int $chatId, int $newMessage): void
    {
        $key = $this->getKey($keyType, $chatId);

        $oldMessage = $this->get($key);
        if ($oldMessage) {
            $this->telegramBotService->deleteMessage($chatId, (int)$oldMessage);
        }

        $this->set($key, $newMessage);
    }

    public function cleanup(string $keyType, int $chatId): void
    {
        $key = $this->getKey($keyType, $chatId);

        $existingMessage = $this->redis->get($key);
        if ($existingMessage) {
            $this->telegramBotService->deleteMessage($chatId, (int)$existingMessage);
        }
    }

    public function setExMessage(string $keyType, int $chatId, int $ttl, int $newMessage): void
    {
        $this->setEx($this->getKey($keyType, $chatId), $ttl, $newMessage);
    }

    public function setMessage(string $keyType, int $chatId, int $newMessage): void
    {
        $this->set($this->getKey($keyType, $chatId), $newMessage);
    }
}
