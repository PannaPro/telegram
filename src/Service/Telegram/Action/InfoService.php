<?php

namespace App\Service\Telegram\Action;

use App\Security\SecurityTelegramUserService;
use App\Service\Telegram\Menu\MenuService;
use App\Service\Telegram\TelegramMessageCache;
use App\Service\TelegramBotService;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

final class InfoService
{
    public function __construct(
        private SecurityTelegramUserService $security,
        private TelegramBotService $telegramBotService,
        private TelegramMessageCache $cache,
    ) {
    }

    public function handle(int $currentMessage): void
    {
        $user = $this->security->fetchCurrentUser();
        $chatId = $user->getChatId();

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

        $inlineKeyboard = new InlineKeyboardMarkup([
            [
                ['text' => 'Категория 1', 'callback_data' => 'info_cat_1'],
                ['text' => 'Категория 2', 'callback_data' => 'info_cat_2'],
            ],
            [
                ['text' => 'Категория 3', 'callback_data' => 'info_cat_3'],
            ],
        ]);

        $message = $this->telegramBotService->sendMessage(
            $chatId,
            $text,
            'Markdown',
            false,
            null,
            $inlineKeyboard
        );

        $this->cache->clear('step', $chatId, $message->getMessageId(), $currentMessage);
    }
}
