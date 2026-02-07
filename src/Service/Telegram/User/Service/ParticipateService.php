<?php

namespace App\Service\Telegram\User\Service;

use App\Repository\TelegramUserRepository;
use App\Security\SecurityTelegramUserService;
use App\Service\Telegram\Enum\TelegramCacheKey;
use App\Service\Telegram\Enum\TelegramDefaultValue;
use App\Service\Telegram\Message\NeedUsernameMessage;
use App\Service\Telegram\Message\ParticipateMessage;
use App\Service\Telegram\Object\DeletableTelegramMessageInterface;
use App\Service\Telegram\Object\NoTelegramMessage;
use App\Service\Telegram\TelegramMessageCache;
use Imagine\Gd\Font;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
use CURLFile;

class ParticipateService
{
    public function __construct(
        private TelegramUserRepository      $telegramUserRepository,
        private SecurityTelegramUserService $security,
        private TelegramMessageCache        $cache,
        private ParticipateMessage          $participateMessage,
        private NeedUsernameMessage         $needUsernameMessage,
    ) {
    }

    public function handleCallbackQuery(string $callbackQueryId): void
    {
        $this->participateMessage->answerCallbackQuery($callbackQueryId);

        $user = $this->security->fetchCurrentUser();
        $chatId = $user->getChatId();
        $username = $user->getUsername();

        $user->setUsername($username);
        $this->telegramUserRepository->save($user);

        if ($username === TelegramDefaultValue::UNKNOWN) {
            $this->needUsernameMessage($chatId);
        } else {
            $this->makeAction(new NoTelegramMessage());
        }
    }

    public function makeAction(DeletableTelegramMessageInterface $currentMessage): void
    {
        $user = $this->security->fetchCurrentUser();
        $chatId = $user->getChatId();
        $username = $user->getUsername();

        $imageBinary = $this->generateImage($user->getId());
        $photo = new CURLFile(
            'data://image/png;base64,' . base64_encode($imageBinary),
            'image/png',
            'participant.png'
        );

        $messageId = $this->participateMessage->sendMessage($chatId, $username, $photo);

        $currentMessage->delete($this->cache, $chatId);
        $this->cache->replaceMessage(TelegramCacheKey::STEP, $chatId, $messageId);
    }

    public function needUsernameMessage(int $chatId): void
    {
        $messageId = $this->needUsernameMessage->sendMessage($chatId);

        $this->cache->deletePreviousMessage(TelegramCacheKey::STEP, $chatId);
        $this->cache->replaceMessage(TelegramCacheKey::START_MENU, $chatId, $messageId);
    }

    private function generateImage(int $chatId): string
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
                return $str;
            }
        }

        return "$str" . ".";
    }
}

