<?php

namespace App\Http\Dto;

use Symfony\Component\Serializer\Attribute\DiscriminatorMap;

#[DiscriminatorMap(
    typeProperty: '_telegram_type',
    mapping: [
        'message' => MessageTelegramPayload::class,
        'my_chat_member' => MyChatMemberPayload::class,
        'callback_query' => CallbackQueryTelegramPayload::class,
        'new_chat_title' => NewChatTitleTelegramPayload::class
    ]
)]
abstract class AbstractPayload implements TelegramUserIdentityInterface
{
    public function __construct(
        public int $update_id,
    ) {
    }
}
