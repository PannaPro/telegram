<?php

namespace App\Service\Telegram\Handler;

trait AnswerCallbackQueryTrait
{
    private function answerCallbackQuery($callbackId): void
    {
        $this->bot->answerCallbackQuery($callbackId);
    }
}
