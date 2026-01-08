# WhatsApp Service (Evolution API)

WhatsApp интеграция для QazEduCRM на базе Evolution API.

## Требования

- Docker Desktop (Windows/Mac) или Docker Engine (Linux)
- Docker Compose v2+

## Быстрый старт

### 1. Настройка

```bash
# Скопируйте файл конфигурации
cp .env.example .env

# Отредактируйте .env и измените API_KEY на свой секретный ключ
```

### 2. Запуск

```bash
# Запустить Evolution API
docker compose up -d

# Проверить статус
docker compose ps

# Посмотреть логи
docker compose logs -f evolution-api
```

### 3. Проверка

После запуска Evolution API доступен по адресу:
- **API**: http://localhost:8080
- **Swagger**: http://localhost:8080/docs

## API Endpoints

### Создание инстанса (подключение WhatsApp)

```bash
curl -X POST 'http://localhost:8080/instance/create' \
  -H 'apikey: your-api-key' \
  -H 'Content-Type: application/json' \
  -d '{
    "instanceName": "clinic_1",
    "qrcode": true,
    "integration": "WHATSAPP-BAILEYS"
  }'
```

### Получение QR-кода

```bash
curl -X GET 'http://localhost:8080/instance/qrcode/clinic_1' \
  -H 'apikey: your-api-key'
```

### Отправка сообщения

```bash
curl -X POST 'http://localhost:8080/message/sendText/clinic_1' \
  -H 'apikey: your-api-key' \
  -H 'Content-Type: application/json' \
  -d '{
    "number": "77001234567",
    "text": "Привет! Это тестовое сообщение."
  }'
```

### Статус подключения

```bash
curl -X GET 'http://localhost:8080/instance/connectionState/clinic_1' \
  -H 'apikey: your-api-key'
```

## Webhooks

Evolution API отправляет события на URL указанный в `WEBHOOK_URL`:

| Событие | Описание |
|---------|----------|
| `QRCODE_UPDATED` | Новый QR-код для сканирования |
| `CONNECTION_UPDATE` | Изменение статуса подключения |
| `MESSAGES_UPSERT` | Новое входящее сообщение |
| `MESSAGES_UPDATE` | Обновление статуса сообщения |
| `SEND_MESSAGE` | Исходящее сообщение отправлено |

### Пример webhook payload (входящее сообщение)

```json
{
  "event": "messages.upsert",
  "instance": "clinic_1",
  "data": {
    "key": {
      "remoteJid": "77001234567@s.whatsapp.net",
      "fromMe": false,
      "id": "ABC123"
    },
    "message": {
      "conversation": "Здравствуйте, хочу записаться на прием"
    },
    "messageTimestamp": 1704067200,
    "pushName": "Иван Петров"
  }
}
```

## Структура папок

```
whatsapp-service/
├── docker-compose.yml    # Docker конфигурация
├── .env                  # Переменные окружения (не в git!)
├── .env.example          # Пример .env
├── README.md             # Документация
└── data/                 # Данные (не в git!)
    ├── instances/        # Сессии WhatsApp
    └── store/            # Хранилище сообщений
```

## Управление

```bash
# Остановить
docker compose stop

# Запустить снова
docker compose start

# Полная остановка и удаление контейнеров
docker compose down

# Перезапуск с пересборкой
docker compose up -d --force-recreate

# Обновить образ до последней версии
docker compose pull
docker compose up -d
```

## Troubleshooting

### QR-код не появляется
- Проверьте логи: `docker compose logs evolution-api`
- Убедитесь что инстанс создан

### Webhook не получает события
- Проверьте что `WEBHOOK_URL` доступен из Docker контейнера
- Для Windows используйте `host.docker.internal` вместо `localhost`

### WhatsApp отключается
- Не используйте один номер на нескольких устройствах
- Избегайте массовых рассылок
- При бане номера - используйте другой номер

## Production

Для production рекомендуется:

1. Изменить `API_KEY` на случайный ключ
2. Настроить HTTPS через reverse proxy (nginx)
3. Использовать PostgreSQL вместо SQLite
4. Добавить Redis для кэширования
5. Настроить бэкапы папки `data/`

## Риски

**ВНИМАНИЕ**: Evolution API использует неофициальный WhatsApp API.
Это может привести к блокировке номера WhatsApp.

Рекомендации:
- Используйте отдельный номер для CRM
- Не делайте массовые рассылки
- Отвечайте на сообщения, а не инициируйте их
