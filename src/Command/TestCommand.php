<?php

namespace App\Command;

use App\Service\Telegram\Admin\Service\AdminReferralService;
use App\Service\Telegram\Context\Dto\ReferralSearchContext;
use App\Service\TelegramBotService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'test',
    description: 'Add a short description for your command',
)]
class TestCommand extends Command
{
    public function __construct(
        private TelegramBotService $bot,
        private AdminReferralService $adminReferralService,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $hash = hash('sha256', 301671507 . ':' . 'thismypassword');
        dd($hash);
        return 1;
    }
}
