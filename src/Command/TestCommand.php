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
        $context = new ReferralSearchContext(301671507 ,'participant_cd_referral');
        $context->setTextType('Статус участник c 17-12-2025 по 19-12-2024 не менее 2 рефералов');
        $context->setDateType('date_range_period');
        $context->setRangeStart('2025-12-17');
        $context->setRangeEnd('2025-12-19');
        $context->setCount(2);

        $data = $this->adminReferralService->downloadResult($context);

        dd($data);
        return 1;
    }
}
