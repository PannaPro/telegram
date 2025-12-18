<?php

namespace App\Service\Telegram\Handler;

trait AnswerCallbackQueryTrait
{
    private function answerCallbackQuery(int $callbackId, ?string $text = null): void
    {
        $this->bot->answerCallbackQuery($callbackId, $text);
    }
}
