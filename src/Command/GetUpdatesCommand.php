<?php

namespace App\Command;

use App\Http\Dto\AbstractPayload;
use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Http\Dto\MessageTelegramPayload;
use App\Http\Dto\MyChatMemberPayload;
use App\Security\SecurityTelegramUserService;
use App\Service\Telegram\Handler\PayloadHandler;
use App\Service\TelegramBotService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand(
    name: 'telegram:get-updates',
    description: 'Get updates from Telegram (polling mode without webhooks)',
)]
class GetUpdatesCommand extends Command
{
    private int $offset = 0;

    public function __construct(
        private TelegramBotService $bot,
        private PayloadHandler $payloadHandler,
        private SecurityTelegramUserService $security,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('watch', null, InputOption::VALUE_NONE, 'Watch mode - keep polling for updates')
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Limit updates per request', 10)
            ->addOption('timeout', null, InputOption::VALUE_OPTIONAL, 'Polling timeout in seconds', 5);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $watch = $input->getOption('watch');
        $limit = (int)$input->getOption('limit');
        $timeout = (int)$input->getOption('timeout');

        $output->writeln('🔄 Starting Telegram polling (testing mode)...');
        $output->writeln("Token: " . substr($this->bot->getBotToken(), 0, 10) . "...");

        if ($watch) {
            $output->writeln('👀 Watch mode enabled (Ctrl+C to stop)');
            $this->watchUpdates($output, $limit, $timeout);
        } else {
            $this->fetchUpdates($output, $limit);
        }

        return 0;
    }

    private function fetchUpdates(OutputInterface $output, int $limit): void
    {
        try {
            $updates = $this->bot->getUpdates($this->offset, $limit);

            if (empty($updates)) {
                $output->writeln('ℹ️ No new updates');
                return;
            }

            $output->writeln("📨 Received " . count($updates) . " update(s)");

            foreach ($updates as $update) {
                $updateId = $update['update_id'] ?? null;

                $output->writeln("─────────────────────");
                $output->writeln("📮 Processing update ID: {$updateId}");

                try {
                    $this->processUpdate($update);
                    $this->offset = $updateId + 1;
                    $output->writeln("✅ Update processed successfully");
                } catch (\Exception $e) {
                    $output->writeln("❌ Error processing update: " . $e->getMessage());
                }
            }

            $output->writeln("─────────────────────");
            $output->writeln("✅ Batch processing complete");
        } catch (\Exception $e) {
            $output->writeln("❌ Error fetching updates: " . $e->getMessage());
        }
    }

    private function watchUpdates(OutputInterface $output, int $limit, int $timeout): void
    {
        while (true) {
            try {
                $updates = $this->bot->getUpdates($this->offset, $limit, $timeout);

                if (!empty($updates)) {
                    $output->writeln("📨 Received " . count($updates) . " update(s) at " . date('Y-m-d H:i:s'));

                    foreach ($updates as $update) {
                        $updateId = $update['update_id'] ?? null;
                        $output->writeln("  🔹 Update ID: {$updateId}");

                        try {
                            $output->writeln("    🔄 Processing...");
                            $this->processUpdate($update, $output);
                            $this->offset = $updateId + 1;
                            $output->writeln("    ✅ Processed");
                        } catch (\Exception $e) {
                            $output->writeln("    ❌ Error: " . $e->getMessage());
                            $output->writeln("    Stack trace: " . $e->getTraceAsString());
                        }
                    }
                }

                // Small delay to avoid overwhelming Telegram API
                usleep(500000); // 0.5 seconds
            } catch (\Exception $e) {
                $output->writeln("❌ Polling error: " . $e->getMessage());
                sleep(5); // Wait before retrying
            }
        }
    }

    private function processUpdate(array $update, OutputInterface $output = null): void
    {
        try {
            // Convert raw update to payload using same logic as webhook
            $payload = $this->createPayloadFromUpdate($update, $output);

            if ($payload) {
                $output?->writeln("    📦 Payload type: " . get_class($payload));
                $output?->writeln("    👤 Chat ID: " . $payload->getChatId());

                try {
                    $this->payloadHandler->handlePayload($payload);
                    $output?->writeln("    ✅ Handler executed successfully");
                } catch (\Throwable $e) {
                    $output?->writeln("    ❌ Handler error: " . $e->getMessage());
                    $output?->writeln("    📍 File: " . $e->getFile() . ':' . $e->getLine());
                    throw $e;
                }
            } else {
                $output?->writeln("    ⚠️ Could not create payload from update");
            }
        } finally {
            // CRITICAL: Clear user context after each update to avoid state pollution
            // in long-running console process (unlike HTTP requests where DI container is fresh)
            $this->security->clearCurrentUser();
            $output?->writeln("    🧹 Context cleared");
        }
    }

    private function createPayloadFromUpdate(array $update, OutputInterface $output = null): ?AbstractPayload
    {
        try {
            if (isset($update['message'])) {
                $output?->writeln("    🔍 Detected: MESSAGE from user {$update['message']['from']['id']}");

                $payload = new MessageTelegramPayload($update['update_id']);
                $payload->message = $update['message'];

                return $payload;
            }
            elseif (isset($update['callback_query'])) {
                $output?->writeln("    🔍 Detected: CALLBACK from user {$update['callback_query']['from']['id']}, data: {$update['callback_query']['data']}");

                $payload = new CallbackQueryTelegramPayload($update['update_id']);
                $payload->callback_query = $update['callback_query'];

                return $payload;
            }
            elseif (isset($update['my_chat_member'])) {
                $output?->writeln("    🔍 Detected: MY_CHAT_MEMBER");

                $payload = new MyChatMemberPayload($update['update_id']);
                $payload->my_chat_member = $update['my_chat_member'];

                return $payload;
            }
            else {
                $output?->writeln("    🔍 Unknown update type");
                return null;
            }
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to create payload from update: " . $e->getMessage(), 0, $e);
        }
    }
}
