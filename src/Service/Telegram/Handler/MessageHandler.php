<?php

namespace App\Service\Telegram\Handler;

use App\Entity\TelegramUser;
use App\Http\Dto\AbstractPayload;
use App\Http\Dto\MessageTelegramPayload;
use App\Repository\TelegramUserRepository;
use App\Service\Telegram\Menu\MenuService;
use App\Service\TelegramBotService;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class MessageHandler
{
    public function __construct(
        private TelegramUserRepository $telegramUserRepository,
        private TelegramBotService $telegramBotService,
        private MenuService $menuService,
    ) {
    }

    /**
     * @param MessageTelegramPayload $dto
     * @return void
     */
    public function makeAction(AbstractPayload $dto): void
    {
        $text = $dto->getText();

        if (!empty($text)) {
            $this->handleCommand($dto, $text);
        }
    }

    private function handleCommand(MessageTelegramPayload $dto, string $command): void
    {
        switch ($command) {
            case '/start':
                $this->startAction($dto);
                break;
            case '/help':
//                $this->helpAction($dto);
                break;
            // Добавляй свои команды
            default:
//                $this->unknownCommand($dto);
        }
    }

    private function startAction(MessageTelegramPayload $dto): void
    {
        $chatId = $dto->getChatId();
        $username = $dto->getUsername();

        $user = $this->telegramUserRepository->findOneBy(['chatId' => $chatId]);
        if (!$user instanceof TelegramUser) {
            $user = new TelegramUser();
            $user
                ->setUsername($username)
                ->setChatId($chatId)
                ->setFirstName($dto->getFirstName())
                ->setLastName($dto->getLastName())
            ;
            $this->telegramUserRepository->save($user);
        }

        $this->menuService->sendMenu($chatId);
    }
}
