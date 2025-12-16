<?php

namespace App\Service\Telegram\Context;

use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\TelegramMessageCache;

class ContextStorage
{
    public function __construct(
        private TelegramMessageCache $cache,
    ) {
    }

    public function hasContext(int $chatId): bool
    {
        return $this->cache->getMessage('context', $chatId) !== false;
    }

    public function getContext(int $chatId): ContextInterface|false
    {
        $json = $this->cache->getMessage('context', $chatId);
        if (!$json) {
            return false;
        }

        $data = json_decode($json, true);

        $dtoClass = $data['class'] ?? null;
        $payload = $data['payload'] ?? null;

        if ($dtoClass && $payload && class_exists($dtoClass)) {
            return $dtoClass::fromArray($payload);
        }

        return false;
    }

    public function setContext(int $chatId, ContextInterface $context, int $ttl = TelegramCacheKey::TTL_10_MINUTES): void
    {
        $data = [
            'class' => get_class($context),
            'payload' => $context->toArray(),
        ];

        $this->cache->setExMessage('context', $chatId, $ttl, json_encode($data));
    }

    public function unsetContext(int $chatId): void
    {
        $this->cache->deleteMessage('context', $chatId);
    }

    public function updateContext(int $chatId, ContextInterface $context, int $ttl = TelegramCacheKey::TTL_5_MINUTES): void
    {
        $data = [
            'class' => get_class($context),
            'payload' => $context->toArray(),
        ];

        $this->cache->setExMessage('context', $chatId, $ttl, json_encode($data));
    }
}
