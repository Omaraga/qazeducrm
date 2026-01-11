<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'bsVersion' => '4.x',

    // Telegram Bot для авторизации в личном кабинете
    'telegramBot' => [
        'token' => '', // Получите у @BotFather
        'username' => '', // Username бота без @
        'webhookUrl' => '', // Будет сгенерирован автоматически если пусто
    ],

    // WhatsApp интеграция (Evolution API)
    'whatsapp' => [
        'apiUrl' => 'http://localhost:8085',
        'apiKey' => 'qazeducrm-dev-api-key-2025',
        // Webhook URL (куда Evolution API будет слать события)
        // Для Docker + XAMPP с VirtualHost: http://host.docker.internal/webhook/whatsapp (+ Host header)
        // Для продакшена: https://yourdomain.com/webhook/whatsapp
        'webhookUrl' => 'http://host.docker.internal/webhook/whatsapp',
        // Host header для Apache VirtualHost
        'webhookHost' => 'educrm.loc',
        'autoCreateLids' => true, // Автоматически создавать лидов из новых контактов
    ],
];
