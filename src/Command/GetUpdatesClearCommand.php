<?php

namespace App\Command;

use App\Service\TelegramBotService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Telegram\Bot\Objects\Update;

#[AsCommand(
    name: 'clear',
    description: 'Clear getUpdate bot messages',
)]
class GetUpdatesClearCommand extends Command
{
    public function __construct(
        private TelegramBotService $telegramBotService,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $updates = $this->telegramBotService->getUpdate();

        if (empty($updates)) {
            $output->writeln('<info>No updates to clear</info>');
            return Command::SUCCESS;
        }

        /** @var Update $lastUpdate */
        $lastUpdate = end($updates);

        $this->telegramBotService->getUpdate($lastUpdateId + 1);


        return Command::SUCCESS;
    }
}
