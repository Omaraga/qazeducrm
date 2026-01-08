<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'bsVersion' => '4.x',

    // WhatsApp интеграция (Evolution API)
    'whatsapp' => [
        'apiUrl' => 'http://localhost:8085',
        'apiKey' => 'qazeducrm-dev-api-key-2025',
        // Webhook URL (куда Evolution API будет слать события)
        // Для Docker: http://host.docker.internal/webhook/whatsapp
        // Для продакшена: https://yourdomain.com/webhook/whatsapp
        'webhookUrl' => 'http://host.docker.internal/webhook/whatsapp',
        // Host header для Apache VirtualHost
        'webhookHost' => 'educrm.loc',
        'autoCreateLids' => true, // Автоматически создавать лидов из новых контактов
    ],
];
