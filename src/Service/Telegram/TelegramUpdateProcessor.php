<?php

namespace App\Service\Telegram;

use App\Http\Dto\CallbackQueryTelegramPayload;
use App\Http\Dto\MessageTelegramPayload;
use App\Http\Dto\MyChatMemberPayload;
use App\Http\Serializer\TelegramPayloadDiscriminatorDenormalizer;
use App\Service\Telegram\Router\AdminPayloadRouter;
use App\Service\Telegram\Router\UserPayloadRouter;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class TelegramUpdateProcessor
{
    public function __construct(
        private SerializerInterface $serializer,
        private TelegramPayloadDiscriminatorDenormalizer $denormalizer,
        private AdminPayloadRouter $adminRouter,
        private UserPayloadRouter $userRouter,
        private LoggerInterface $logger,
    )
    {
    }

    public function process(array $update): void
    {
        $this->logger->info('Processing Telegram update', ['update_id' => $update['update_id'] ?? null]);

        try {
            // Deserialize the update to appropriate payload class
            $payload = $this->deserializeUpdate($update);

            if (!$payload) {
                $this->logger->debug('No payload created from update', ['update' => $update]);
                return;
            }

            $chatId = $payload->getChatId();
            $this->logger->info('Update deserialized', ['chat_id' => $chatId, 'payload_type' => get_class($payload)]);

            // Determine if user is admin or regular user
            // For now, route to admin router - you can add logic to determine user type
            $this->adminRouter->route($payload);

        } catch (\Exception $e) {
            $this->logger->error('Error processing update', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'update' => $update,
            ]);
            throw $e;
        }
    }

    private function deserializeUpdate(array $update): ?object
    {
        $updateArray = [
            'update_id' => $update['update_id'] ?? null,
        ];

        // Check for different update types and build appropriate payload
        if (isset($update['message'])) {
            $updateArray['message'] = $update['message'];
            $dtoClass = MessageTelegramPayload::class;
        } elseif (isset($update['callback_query'])) {
            $updateArray['callback_query'] = $update['callback_query'];
            $dtoClass = CallbackQueryTelegramPayload::class;
        } elseif (isset($update['my_chat_member'])) {
            $updateArray['my_chat_member'] = $update['my_chat_member'];
            $dtoClass = MyChatMemberPayload::class;
        } else {
            $this->logger->debug('Unknown update type', ['update' => $update]);
            return null;
        }

        try {
            $payload = $this->serializer->deserialize(
                json_encode($updateArray),
                $dtoClass,
                'json'
            );
            return $payload;
        } catch (\Exception $e) {
            $this->logger->error('Deserialization error', [
                'error' => $e->getMessage(),
                'dto_class' => $dtoClass,
            ]);
            throw $e;
        }
    }
}
