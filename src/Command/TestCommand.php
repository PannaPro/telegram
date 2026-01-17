<?php

namespace App\Command;

use App\Repository\EventRepository;
use App\Service\Telegram\Admin\Service\AdminGameService;
use App\Service\Telegram\Admin\Service\AdminReferralService;
use App\Service\Telegram\Context\Dto\ReferralSearchContext;
use App\Service\TelegramBotService;
use DateTimeImmutable;
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
        private AdminGameService $adminGameService,
        private EventRepository $eventRepository,
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
        $chatId = 301671507; // Your Telegram user ID
        
        $output->writeln('🚀 Запуск теста создания события...');
        
        try {
            // Start creating event
            $this->adminGameService->createEvent($chatId, 0);
            $output->writeln('✅ Инициализация контекста создания события успешна!');
            $output->writeln('📝 Отправлено сообщение с выбором категории события.');
        } catch (\Exception $e) {
            $output->writeln('❌ Ошибка: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }

    private function formatEvents(array $events): array
    {
        $normalize = function(array $list) {
            return array_map(function($event) {
                return [
                    'name' => $event['eventName'],
                    'from' => $event['periodFrom'] instanceof \DateTimeInterface
                        ? $event['periodFrom']->format('d-m-Y H:i')
                        : null,
                    'to' => $event['periodTo'] instanceof \DateTimeInterface
                        ? $event['periodTo']->format('d-m-Y H:i')
                        : null,
                    'isActive' => $event['isActive'],
                ];
            }, $list);
        };

        return [
            'current' => isset($events['current']) ? $normalize($events['current']) : [],
            'upcoming' => isset($events['upcoming']) ? $normalize($events['upcoming']) : [],
        ];
    }
}
