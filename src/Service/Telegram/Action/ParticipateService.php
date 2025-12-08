<?php

namespace App\Service\Telegram\Action;

use App\Repository\TelegramUserRepository;
use App\Security\SecurityTelegramUserService;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Enum\TelegramDefaultValue;
use App\Service\Telegram\Enum\TelegramParseMode;
use App\Service\Telegram\TelegramMessageCache;
use Imagine\Gd\Font;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use App\Service\TelegramBotService;
use CURLFile;

class ParticipateService
{
    public function __construct(
        private TelegramUserRepository $telegramUserRepository,
        private SecurityTelegramUserService $security,
        private TelegramBotService $telegramBotService,
        private TelegramMessageCache $cache,
    ) {
    }

    public function handle(int $chatId): void
    {
        $photoPath = '/app/public/image/paketa.jpg';

        $caption = <<<MARKDOWN
        Итак, все наши игры проходят в стороннем боте КЛИК.

        Чтобы я смог идентифицировать вас в случае победы, необходимо загрузить в этом приложении аватарку с вашим порядковым номером, который выдаст бот после нажатия кнопки «Получить номер».

        Пожалуйста, сделайте это в первую очередь – без этого я не смогу вас узнать и выдать приз.

        Ниже я прикреплю пошаговую инструкцию, как всё настроить. Это очень просто.

        [Клик для запуска игры](https://t.me/gamee/start?startapp=eyJyZWYiOjM3NDA2OTk5NH0)
        MARKDOWN;

        $keyboard = new ReplyKeyboardMarkup(
            [
                ['🎲 Принять участие'],
                ['💡 Инфо'],
            ],
            true,
            true,
            true
        );

        $message = $this->telegramBotService->sendPhoto(
            $chatId,
            new CURLFile($photoPath),
            $caption,
            null,
            $keyboard,
            false,
            TelegramParseMode::MARKDOWN
        );

        $this->cache->saveAndCleanup(TelegramCacheKey::STEP, $chatId, $message->getMessageId());
    }

    public function handleCallbackQuery(): void
    {
        $user = $this->security->fetchCurrentUser();
        $chatId = $user->getChatId();
        $username = $user->getUsername();

        $user->setUsername($username);
        $this->telegramUserRepository->save($user);

        if ($username === TelegramDefaultValue::UNKNOWN) {
            $this->needUsernameMessage($chatId);
        } else {
            $this->participateMessage();
        }
    }

    public function needUsernameMessage(int $chatId): void
    {
        $text = <<<MARKDOWN
        😅 Ой! Похоже, у тебя не указан юзернейм.

        Чтобы его добавить:
        1️⃣ Перейди в настройки Telegram.
        2️⃣ Найди поле "Имя пользователя".
        3️⃣ Придумай уникальный юзернейм и сохрани изменения.

        После этого сможешь участвовать в играх и получать призы! 🎉
        MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => '🎲 Участвовать', 'callback_data' => 'participate']
            ]
        ]);

        $message = $this->telegramBotService->sendMessage(
            $chatId,
            $text,
            TelegramParseMode::MARKDOWN,
            false,
            null,
            $keyboard
        );

        $this->cache->saveAndCleanup(TelegramCacheKey::STEP, $chatId, $message->getMessageId());
    }

    public function participateMessage(int $currentMessage = 0): void
    {
        $user = $this->security->fetchCurrentUser();
        $chatId = $user->getChatId();
        $username = $user->getUsername();
        $imageBinary = $this->generateImage($user->getId());

        $caption = <<<MARKDOWN
        @$username, все наши игры проходят в боте [Gamee](https://t.me/gamee/start?startapp=eyJyZWYiOjM3NDA2OTk5NH0)

        Я сгенерировал для тебя аватарку с твоим игровым номером — она прикреплена выше.

        Установи ee в игровом боте (в левом верхнем углу), чтобы я мог точно определить тебя в случае победы и выдать приз.

        👉 [Открыть игру и установить аватарку](https://t.me/gamee/start?startapp=eyJyZWYiOjM3NDA2OTk5NH0)

        ⚠️Без установленной аватарки мы не сможем засчитать участие.
        MARKDOWN;

        $keyboard = new InlineKeyboardMarkup([
            [
                ['text' => "Готово!", 'callback_data' => 'avatarSet'],
            ],
        ]);

        $photo = new CURLFile(
            'data://image/png;base64,' . base64_encode($imageBinary),
            'image/png',
            'participant.png'
        );

        $message = $this->telegramBotService->sendPhoto(
            $chatId,
            $photo,
            $caption,
            null,
            $keyboard,
            false,
            TelegramParseMode::MARKDOWN
        );

        $this->cache->clear(TelegramCacheKey::STEP, $chatId, $currentMessage, $message->getMessageId());
    }

    public function generateImage(int $chatId): string
    {
        $text = $this->rotationNumber($chatId);

        $imagine = new Imagine();

        $width = 600;
        $height = 600;

        $size = new Box($width, $height);
        $palette = new RGB();
        $white = $palette->color('#ffffff');
        $red = $palette->color('#ff0000');
        $image = $imagine->create($size, $white);

        $fontFile = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
        $fontSize = 64;

        $font = new Font($fontFile, $fontSize, $red);
        $box = $font->box($text);
        $x = ( $width  - $box->getWidth() ) / 2;
        $y = ( $height - $box->getHeight() ) / 2;

        $image->draw()->text($text, $font, new Point($x, $y));

        return $image->get('png');
    }

    private function rotationNumber(int $number): string
    {
        $str = (string)$number;

        for ($i = 0; $i < strlen($str); $i++) {
            if ($str[$i] !== '6' && $str[$i] !== '9') {
                return $str; // безопасное число
            }
        }

        return "$str" . ".";
    }
}

