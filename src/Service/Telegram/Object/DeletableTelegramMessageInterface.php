<?php

namespace App\Service\Telegram\Object;

use App\Service\Telegram\Cache\TelegramMessageCache;

interface DeletableTelegramMessageInterface
{
    public function delete(TelegramMessageCache $cache, int $chatId): void;
}
