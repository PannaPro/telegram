<?php

namespace App\Service\Telegram\Handler\Lock;

class PayloadLockManager
{
    // TODO новая архитектура
//src/
//└── Service/
//└── Telegram/
//├── Router/                        # Входные обработчики
//│   ├── PayloadHandler.php          # Главная точка входа: лок + вызов роутеров
//│   ├── Lock/
//│   │   └── PayloadLockManager.php  # Локи по chatId (Redis)
//│   └── Router/
//│       ├── AdminPayloadRouter.php  # Роутер админа
//│       └── UserPayloadRouter.php   # Роутер юзера
//│
//├── Context/                         # Контексты и хранилище
//│   ├── ContextInterface.php         # Общий интерфейс для всех DTO контекстов
//│   ├── ContextStorage.php           # Работа с Redis
//│   ├── Admin/                       # Контексты админа
//│   │   ├── ReferralSearchContextDto.php
//│   │   ├── PasswordWaitingContextDto.php
//│   │   └── ... другие контексты
//│   └── User/                        # Контексты юзера (если будут)
//│       └── UserSomeContextDto.php
//│
//├── Admin/                            # Вся логика админа
//│   ├── Router/
//│   │   ├── AdminMessageHandler.php           # Сообщения админа
//│   │   ├── AdminCallbackQueryHandler.php    # Колбеки админа
//│   │   └── AdminContextHandler.php          # Контекст-хендлер админа
//│   ├── AdminAction/
//│   │   ├── AdminMenuService.php
//│   │   ├── AdminReferralService.php
//│   │   ├── ReferralSearch/
//│   │   │   ├── ReferralSearchService.php
//│   │   │   └── ReferralSearchStorage.php
//│   │   └── AdminSessionService.php
//│   └── Keyboard/
//│       └── AdminKeyboardFactory.php
//│
//├── User/                             # Логика обычного юзера
//│   ├── Router/
//│   │   ├── UserMessageHandler.php
//│   │   ├── UserCallbackQueryHandler.php
//│   │   └── UserContextHandler.php           # Контекст-хендлер юзера
//│   ├── Action/
//│   └── Keyboard/
//│       └── UserKeyboardFactory.php
//│
//├── MyChatMember/
//│   └── MyChatMemberService.php
//│
//├── Keyboard/
//│   └── InlineKeyboardBuilder.php
//│
//├── Security/
//│   └── SecurityTelegramUserService.php
//│
//└── Common/
//└── UnknownCommandService.php

}
