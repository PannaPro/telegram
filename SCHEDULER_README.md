# Event Status Scheduler

Система автоматического управления статусами событий.

## Описание

Каждые 10 минут система проверяет события и автоматически:
- **Активирует** события (isActive = true), если их время начала наступило
- **Деактивирует** события (isActive = false), если их период действия истек

## Компоненты системы

### 1. Scheduler (Планировщик)
- **Файл**: `src/Scheduler/EventStatusScheduleProvider.php`
- **Частота**: Каждые 10 минут
- **Действие**: Создает задачу в Redis очередь

### 2. Message (Сообщение очереди)
- **Файл**: `src/Message/ManageEventStatusMessage.php`
- **Содержит**: Время запуска задачи

### 3. MessageHandler (Обработчик)
- **Файл**: `src/MessageHandler/ManageEventStatusMessageHandler.php`
- **Действие**: Принимает задачу из очереди и вызывает сервис

### 4. Service (Сервис бизнес-логики)
- **Файл**: `src/Service/Scheduler/SchedulerManageEventService.php`
- **Логика**:
  - Находит события, которые должны стать активными
  - Находит события, которые должны стать неактивными
  - Обновляет статусы в БД
  - Логирует все изменения

## Логирование

Все логи записываются в отдельный файл:
- **Путь**: `var/log/event_scheduler.log`
- **Канал**: `event_scheduler`
- **Ротация**: 7 дней

### Что логируется:
- Время запуска задачи
- Каждое изменение статуса события (ID, название, новый статус, период действия)
- Общее количество активированных/деактивированных событий
- Время завершения задачи

### Пример логов:
```
[2026-01-24 15:30:00] event_scheduler.INFO: Event scheduler task started {"triggered_at":"2026-01-24 15:30:00"}
[2026-01-24 15:30:01] event_scheduler.INFO: Event activated {"event_id":5,"event_name":"Новогодний турнир","new_status":"active","period_from":"2026-01-24 15:00:00","period_to":"2026-01-30 18:00:00"}
[2026-01-24 15:30:01] event_scheduler.INFO: Event deactivated {"event_id":3,"event_name":"Старый турнир","new_status":"inactive","period_from":"2026-01-10 10:00:00","period_to":"2026-01-24 15:00:00"}
[2026-01-24 15:30:01] event_scheduler.INFO: Event statuses updated {"activated_count":1,"deactivated_count":1,"total_updated":2}
[2026-01-24 15:30:01] event_scheduler.INFO: Event scheduler task completed {"completed_at":"2026-01-24 15:30:01"}
```

## Docker контейнеры

### scheduler
- **Команда**: `php bin/console messenger:consume scheduler -vv`
- **Назначение**: Запускает планировщик задач
- **Restart**: unless-stopped

### messenger-worker
- **Команда**: `php bin/console messenger:consume event_status -vv`
- **Назначение**: Обрабатывает очередь задач из Redis
- **Restart**: unless-stopped

## Запуск системы

### 1. Запуск всех контейнеров
```bash
docker compose up -d
```

### 2. Проверка работы воркеров
```bash
# Проверить логи scheduler
docker compose logs -f scheduler

# Проверить логи messenger-worker
docker compose logs -f messenger-worker
```

### 3. Просмотр логов событий
```bash
# Внутри контейнера
docker compose exec app tail -f var/log/event_scheduler.log

# Или через хост
tail -f var/log/event_scheduler.log
```

### 4. Ручной запуск задачи (для тестирования)
```bash
docker compose exec app php bin/console messenger:dispatch 'App\Message\ManageEventStatusMessage'
```

## Настройки

### Изменить частоту запуска

Отредактируйте файл `src/Scheduler/EventStatusScheduleProvider.php`:
```php
RecurringMessage::every(
    '10 minutes',  // Измените на нужный интервал: '5 minutes', '1 hour', и т.д.
    new ManageEventStatusMessage(new \DateTimeImmutable())
)
```

### Изменить уровень логирования

Отредактируйте `config/packages/monolog.yaml`:
```yaml
event_scheduler:
    level: debug  # info, warning, error
```

## Мониторинг

### Проверка очереди Redis
```bash
docker compose exec redis redis-cli
> KEYS *event_status*
> LLEN messages/event_status
```

### Проверка статуса воркеров
```bash
docker compose ps
```

### Проверка последних изменений в БД
```sql
SELECT id, name, is_active, period_from, period_to
FROM event
ORDER BY updated_at DESC
LIMIT 10;
```

## Troubleshooting

### Воркер не запускается
1. Проверьте логи: `docker compose logs scheduler messenger-worker`
2. Проверьте Redis подключение
3. Убедитесь что все миграции выполнены

### Задачи не выполняются
1. Проверьте что scheduler контейнер запущен
2. Проверьте очередь в Redis
3. Проверьте логи event_scheduler.log

### События не обновляются
1. Проверьте что messenger-worker работает
2. Проверьте логи для ошибок БД
3. Убедитесь что периоды событий корректны
