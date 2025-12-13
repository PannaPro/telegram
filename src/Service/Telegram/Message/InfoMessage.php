<?php

namespace App\Service\Telegram\Message;

use App\Service\Telegram\Enum\TelegramParseMode;
use App\Service\TelegramBotService;

class InfoMessage
{
    public function __construct(
        private TelegramBotService $bot,
    ) {
    }

    public function sendInfo(int $chatId): int
    {
        $text = <<<MARKDOWN
        📜 *Правила использования бота*

        1️⃣ В игровом боте [Gamee](https://t.me/gamee/start?startapp=eyJyZWYiOjM3NDA2OTk5NH0) должна быть установлена игровая аватарка.
        Вы получили ее при регистрации, так же можете получить ее еще раз кликнув по кнопке Получить номер

        2️⃣ Запрещено:
        - использовать нецензурную лексику 🛑
        - применять любые софты для накрутки результатов игр ⚠️

        3️⃣ Нарушения:
        - Любое подозрение на нарушение может привести к бану (вплоть до пожизненного)
        - В зависимости от нарушения игроку могут быть показаны:
          - 🟨 Желтая карточка — может повлечь временное ограничение доступа к играм
          - 🟥 Красная карточка — более серьезное ограничение, вплоть до полного бана

        ❗ Соблюдай эти простые правила, чтобы наслаждаться честной игрой

        *Возникли вопросы?* Техподдержка: [@PAKETABKOCMOC](https://t.me/PAKETABKOCMOC)
        MARKDOWN;

        $message = $this->bot->sendMessage(
            $chatId,
            $text,
            TelegramParseMode::MARKDOWN,
        );

        return $message->getMessageId();
    }
}
