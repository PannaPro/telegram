<?php

namespace App\Service\Telegram\Object;

use App\Service\Telegram\TelegramMessageCache;

final class NoTelegramMessage implements DeletableTelegramMessageInterface
{
    public function delete(TelegramMessageCache $cache, int $chatId): void
    {

    }
}
