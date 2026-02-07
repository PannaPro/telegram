<?php

namespace App\Service\Telegram\Cache;

use App\Service\Cache\CacheInterface;
use App\Service\TelegramBotMessaging\BotMessengerInterface;

final class TelegramMessageCache
{
    public function __construct(
        private BotMessengerInterface $bot,
        private CacheInterface $cache,
    ) {
    }

    private function getKey(string $keyType, int $keyValue): string
    {
        return "$keyType:$keyValue";
    }

    public function setEx(string $keyType, int $keyValue, int $ttl, mixed $value): void
    {
        $this->cache->setEx($this->getKey($keyType, $keyValue), $ttl, $value);
    }

    public function get(string $keyType, int $keyValue): mixed
    {
        return $this->cache->get($this->getKey($keyType, $keyValue));
    }

    public function set(string $keyType, int $keyValue, mixed $value): void
    {
        $this->cache->set($this->getKey($keyType, $keyValue), $value);
    }

    public function exist(string $keyType, int $keyValue): bool
    {
        return $this->cache->exists($this->getKey($keyType, $keyValue));
    }

    public function delete(string $keyType, int $keyValue): void
    {
        $this->cache->delete($this->getKey($keyType, $keyValue));
    }

    public function setMessage(string $type, int $key, int $messageId): void
    {
        $this->cache->hSet($type, (string)$key, $messageId);
    }

    public function getMessage(string $type, int $key): ?int
    {
        $value = $this->cache->hGet($type, (string)$key);

        return $value !== false ? (int)$value : null;
    }

    public function deletePreviousMessage(string $type, int $key): void
    {
        $messageId = $this->getMessage($type, $key);

        if ($messageId) {
            $this->bot->deleteMessage($key, $messageId);
            $this->cache->hDel($type, (string)$key);
        }
    }

    public function deleteCurrentMessage(int $chatId, int $messageId): void
    {
        $this->selfDestructMessage($chatId, $messageId);
    }

    public function replaceMessage(string $type, int $key, int $newMessageId): void
    {
        $oldMessageId = $this->getMessage($type, $key);
        if ($oldMessageId) {
            $this->bot->deleteMessage($key, $oldMessageId);
        }

        $this->setMessage($type, $key, $newMessageId);
    }

    public function selfDestructMessage(int $chatId, int $messageId): void
    {
        $this->bot->deleteMessage($chatId, $messageId);
    }
}
