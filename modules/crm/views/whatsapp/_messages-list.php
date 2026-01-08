<?php

use app\models\WhatsappMessage;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var WhatsappMessage[] $messages */

if (empty($messages)): ?>
    <div class="flex items-center justify-center h-full">
        <div class="text-center text-[var(--wa-text-secondary)]">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <p>Нет сообщений</p>
        </div>
    </div>
<?php else: ?>
    <div class="space-y-1">
        <?php
        $lastDate = null;
        foreach ($messages as $message):
            // Date separator
            $messageDate = date('Y-m-d', strtotime($message->created_at));
            if ($lastDate !== $messageDate):
                $lastDate = $messageDate;
                $dateLabel = $messageDate === date('Y-m-d') ? 'Сегодня' :
                    ($messageDate === date('Y-m-d', strtotime('-1 day')) ? 'Вчера' :
                    Yii::$app->formatter->asDate($message->created_at, 'd MMMM yyyy'));
        ?>
            <div class="flex justify-center my-3">
                <span class="px-3 py-1 text-xs bg-white/80 text-[var(--wa-text-secondary)] rounded-lg shadow-sm">
                    <?= $dateLabel ?>
                </span>
            </div>
        <?php endif; ?>

        <?php
            $isOutgoing = $message->is_from_me;
            $time = date('H:i', strtotime($message->created_at));
        ?>
        <div class="flex <?= $isOutgoing ? 'justify-end' : 'justify-start' ?>">
            <div class="relative max-w-[65%] px-3 py-1.5 shadow-sm <?= $isOutgoing ? 'wa-message-out wa-message-tail-out' : 'wa-message-in wa-message-tail-in' ?>">
                <?php
                // Render content based on message type
                switch ($message->message_type):
                    case 'image':
                        $mediaUrl = $message->media_url ?: ($message->getInfo('mediaUrl') ?? '');
                        $caption = $message->getInfo('caption') ?? '';
                        if ($mediaUrl): ?>
                            <div class="mb-1">
                                <img src="<?= Html::encode($mediaUrl) ?>" alt="Image"
                                     class="max-w-full rounded cursor-pointer hover:opacity-90"
                                     onclick="window.open(this.src, '_blank')"
                                     style="max-height: 300px;">
                            </div>
                            <?php if ($caption): ?>
                                <p class="text-sm text-[var(--wa-text-primary)] whitespace-pre-wrap break-words"><?= Html::encode($caption) ?></p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-sm text-[var(--wa-text-secondary)] italic">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Изображение
                            </p>
                        <?php endif;
                        break;

                    case 'video':
                        $mediaUrl = $message->media_url ?: ($message->getInfo('mediaUrl') ?? '');
                        if ($mediaUrl): ?>
                            <div class="mb-1">
                                <video controls class="max-w-full rounded" style="max-height: 300px;">
                                    <source src="<?= Html::encode($mediaUrl) ?>" type="video/mp4">
                                </video>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-[var(--wa-text-secondary)] italic">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                Видео
                            </p>
                        <?php endif;
                        break;

                    case 'audio':
                    case 'ptt':
                        $mediaUrl = $message->media_url ?: ($message->getInfo('mediaUrl') ?? '');
                        if ($mediaUrl): ?>
                            <div class="flex items-center gap-2">
                                <audio controls class="h-10" style="min-width: 200px;">
                                    <source src="<?= Html::encode($mediaUrl) ?>" type="audio/ogg">
                                </audio>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-[var(--wa-text-secondary)] italic">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                </svg>
                                Голосовое сообщение
                            </p>
                        <?php endif;
                        break;

                    case 'document':
                        $mediaUrl = $message->media_url ?: ($message->getInfo('mediaUrl') ?? '');
                        $fileName = $message->getInfo('fileName') ?? 'Документ';
                        ?>
                        <a href="<?= Html::encode($mediaUrl ?: '#') ?>" target="_blank"
                           class="flex items-center gap-2 p-2 bg-gray-50 rounded hover:bg-gray-100 transition-colors">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="text-sm text-blue-600 truncate"><?= Html::encode($fileName) ?></span>
                        </a>
                        <?php
                        break;

                    case 'sticker':
                        $mediaUrl = $message->media_url ?: ($message->getInfo('mediaUrl') ?? '');
                        if ($mediaUrl): ?>
                            <img src="<?= Html::encode($mediaUrl) ?>" alt="Sticker" class="w-24 h-24 object-contain">
                        <?php else: ?>
                            <span class="text-4xl">🎉</span>
                        <?php endif;
                        break;

                    case 'location':
                        $lat = $message->getInfo('latitude');
                        $lng = $message->getInfo('longitude');
                        if ($lat && $lng): ?>
                            <a href="https://www.google.com/maps?q=<?= $lat ?>,<?= $lng ?>" target="_blank"
                               class="block p-2 bg-gray-50 rounded hover:bg-gray-100 transition-colors">
                                <div class="flex items-center gap-2">
                                    <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                    </svg>
                                    <span class="text-sm text-blue-600">Открыть на карте</span>
                                </div>
                            </a>
                        <?php else: ?>
                            <p class="text-sm text-[var(--wa-text-secondary)] italic">Местоположение</p>
                        <?php endif;
                        break;

                    case 'contact':
                        $contactName = $message->getInfo('displayName') ?? 'Контакт';
                        ?>
                        <div class="flex items-center gap-2 p-2 bg-gray-50 rounded">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="text-sm"><?= Html::encode($contactName) ?></span>
                        </div>
                        <?php
                        break;

                    default: // text
                        ?>
                        <p class="text-sm text-[var(--wa-text-primary)] whitespace-pre-wrap break-words"><?= Html::encode($message->content) ?></p>
                        <?php
                        break;
                endswitch;
                ?>

                <!-- Time and status -->
                <div class="flex items-center justify-end gap-1 mt-0.5 -mb-0.5">
                    <span class="text-[11px] text-[var(--wa-text-secondary)]"><?= $time ?></span>
                    <?php if ($isOutgoing): ?>
                        <?php
                        $checkClass = 'wa-check';
                        $checkIcon = '';
                        switch ($message->status):
                            case 'read':
                                $checkClass = 'wa-check-read';
                                $checkIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7M5 13l4 4L19 7"/>'; // double check
                                break;
                            case 'delivered':
                                $checkIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7M5 13l4 4L19 7"/>'; // double check gray
                                break;
                            default: // sent
                                $checkIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>'; // single check
                        endswitch;
                        ?>
                        <svg class="w-4 h-4 <?= $checkClass ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <?= $checkIcon ?>
                        </svg>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
