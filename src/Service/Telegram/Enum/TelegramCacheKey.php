<?php

namespace App\Service\Telegram\Enum;

class TelegramCacheKey
{
    public const REFERRAL_WINDOW = 'referralWindow';
    public const START_MENU = 'startMenu';
    public const STEP = 'step';
    public const SUBSCRIPTION = 'subscription';
    public const LAST_UPDATE = 'last_update';
    public const CONTEXT_MESSAGE = 'context_message';
    public const CONTEXT = 'context';
    public const ADMIN_SESSION = 'admin_session';
    public const ERROR_MESSAGE = 'error_message';

    public const TTL_1_HOUR = 3600;
    public const TTL_5_MINUTES = 360;
    public const TTL_10_MINUTES = 600;
    public const TTL_24_HOURS = 86400;
    public const TTL_48_HOURS = 172800;

    public const REFERRAL_SEARCH = 'admin_referral_search';
}
