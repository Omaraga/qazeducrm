<?php

use app\models\WhatsappMessage;
use yii\helpers\Html;

/** @var WhatsappMessage $message */

$isOutgoing = $message->is_from_me;
?>

<div class="flex <?= $isOutgoing ? 'justify-end' : 'justify-start' ?>">
    <div class="max-w-[70%] <?= $isOutgoing ? 'bg-green-500 text-white' : 'bg-white' ?> rounded-lg px-4 py-2 shadow">
        <?php if ($message->message_type !== WhatsappMessage::TYPE_TEXT): ?>
            <!-- Медиа сообщение -->
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-5 h-5 <?= $isOutgoing ? 'text-green-100' : 'text-gray-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <?php if ($message->message_type === WhatsappMessage::TYPE_IMAGE): ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    <?php elseif ($message->message_type === WhatsappMessage::TYPE_VIDEO): ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    <?php elseif ($message->message_type === WhatsappMessage::TYPE_AUDIO): ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                    <?php elseif ($message->message_type === WhatsappMessage::TYPE_DOCUMENT): ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    <?php else: ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    <?php endif; ?>
                </svg>
                <span class="text-sm <?= $isOutgoing ? 'text-green-100' : 'text-gray-500' ?>">
                    <?= Html::encode($message->getPreview()) ?>
                </span>
            </div>
        <?php endif; ?>

        <?php if ($message->content): ?>
            <p class="whitespace-pre-wrap"><?= Html::encode($message->content) ?></p>
        <?php endif; ?>

        <div class="flex items-center justify-end gap-1 mt-1">
            <span class="text-xs <?= $isOutgoing ? 'text-green-100' : 'text-gray-400' ?>">
                <?= $message->getFormattedDate() ?>
            </span>

            <?php if ($isOutgoing): ?>
                <!-- Статус доставки -->
                <?php if ($message->status === WhatsappMessage::STATUS_READ): ?>
                    <svg class="w-4 h-4 text-green-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                <?php elseif ($message->status === WhatsappMessage::STATUS_DELIVERED): ?>
                    <svg class="w-4 h-4 text-green-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                <?php elseif ($message->status === WhatsappMessage::STATUS_SENT): ?>
                    <svg class="w-4 h-4 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
