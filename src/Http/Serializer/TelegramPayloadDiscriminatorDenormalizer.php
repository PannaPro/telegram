<?php

namespace App\Http\Serializer;

use App\Http\Dto\AbstractPayload;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class TelegramPayloadDiscriminatorDenormalizer implements DenormalizerInterface
{
    public function __construct(
        private DenormalizerInterface $denormalizer
    ) {
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return $type === AbstractPayload::class;
    }

    public function denormalize($data, $type, $format = null, array $context = []): mixed
    {
        if (isset($data['message'])) {
            $data['_telegram_type'] = 'message';
        } elseif (isset($data['callback_query'])) {
            $data['_telegram_type'] = 'callback_query';
        } elseif (isset($data['inline_query'])) {
            $data['_telegram_type'] = 'inline_query';
        } elseif (isset($data['my_chat_member'])) {
            $data['_telegram_type'] = 'my_chat_member';
        } else {
            throw new \RuntimeException('Unknown Telegram update type');
        }

        return $this->denormalizer->denormalize(
            $data,
            $type,
            $format,
            $context
        );
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            AbstractPayload::class => true,
            '*' => false,
        ];
    }
}
