<?php

namespace App\Service\Telegram;

use App\Service\TelegramBotService;
use Psr\Log\LoggerInterface;
use Redis;

final class TelegramMessageCache
{
    public function __construct(
        private Redis $redis,
        private TelegramBotService $telegramBotService,
        private LoggerInterface $logger
    ) {
    }

    private function getKey(string $type, int $chatId): string
    {
        return "$type:$chatId";
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

    public function setEx(string $type, int $chatId, int $ttl, mixed $value): void
    {
        $this->redis->setEx($this->getKey($type, $chatId), $ttl, $value);
    }

    public function get(string $type, int $chatId): mixed
    {
        $value = $this->redis->get($this->getKey($type, $chatId));

        return $value;
    }
}
