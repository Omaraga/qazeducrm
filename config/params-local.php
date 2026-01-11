<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Education CRM',
    'bsVersion' => '4.x',

    // Telegram Bot для авторизации в личном кабинете
    'telegramBot' => [
        'token' => '8336436870:AAFvZgtMPfllwq5KhHPG9rs3H3kZeAoqSQU',
        'username' => 'qazeducrmbot',
        'webhookUrl' => '', // Будет сгенерирован автоматически если пусто
    ],

    // WhatsApp интеграция (Evolution API)
    'whatsapp' => [
        'apiUrl' => getenv('EVOLUTION_API_URL') ?: 'http://evolution-api:8080',
        'apiKey' => getenv('EVOLUTION_API_KEY') ?: 'qazeducrm-dev-api-key-2025',
        // Webhook URL (куда Evolution API будет слать события)
        'webhookUrl' => 'http://nginx/1/whatsapp/webhook',
        'webhookHost' => 'crm.qazaq.education',
        'autoCreateLids' => true, // Автоматически создавать лидов из новых контактов
    ],
];
