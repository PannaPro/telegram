<?php

namespace App\Command;

use App\Security\AdminSessionService;
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
        private AdminSessionService $adminSessionService,
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
//        $this->redis->set('action', '/start', 360);

//        $data = $this->redis->get('action');
//
//        dd($data);

//        $this->referralSearchService->prepareToSearch(301671507, 'participantReferral', 421);

        $this->adminSessionService->activateWaitingPassword();
        return Command::SUCCESS;
    }
}
