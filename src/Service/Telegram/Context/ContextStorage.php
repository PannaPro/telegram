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
        return $this->cache->exist(TelegramCacheKey::CONTEXT, $chatId) !== false;
    }

    public function getContext(int $chatId): ContextInterface|false
    {
        $json = $this->cache->get(TelegramCacheKey::CONTEXT, $chatId);

        $data = json_decode($json, true);

        $dtoClass = $data['class'] ?? null;
        $payload = $data['payload'] ?? null;

        if ($dtoClass && $payload && class_exists($dtoClass)) {
            return $dtoClass::fromArray($payload);
        }

        return false;
    }

    public function setContext(int $chatId, ContextInterface $context, int $ttl = TelegramCacheKey::TTL_1_HOUR): void
    {
        $data = [
            'class' => get_class($context),
            'payload' => $context->toArray(),
        ];

        $this->cache->setEx(TelegramCacheKey::CONTEXT, $chatId, $ttl, json_encode($data));
    }

    public function unsetContext(int $chatId): void
    {
        $this->cache->delete(TelegramCacheKey::CONTEXT, $chatId);
    }

    public function updateContext(int $chatId, ContextInterface $context, int $ttl = TelegramCacheKey::TTL_1_HOUR): void
    {
        $data = [
            'class' => get_class($context),
            'payload' => $context->toArray(),
        ];

        $this->cache->setEx(TelegramCacheKey::CONTEXT, $chatId, $ttl, json_encode($data));
    }
}
