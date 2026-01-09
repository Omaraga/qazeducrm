<?php

use app\models\WhatsappMessage;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var WhatsappMessage[] $messages */
/** @var bool $hasMore */
/** @var int|null $oldestId */

$hasMore = $hasMore ?? false;
$oldestId = $oldestId ?? null;

if (empty($messages)): ?>
    <div class="flex items-center justify-center h-full py-16">
        <div class="text-center text-gray-400">
            <svg class="w-16 h-16 mx-auto mb-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <p class="text-base">Нет сообщений</p>
            <p class="text-sm mt-1">Начните диалог первым</p>
        </div>
    </div>
<?php else: ?>
    <div class="space-y-4 px-4 py-2">
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
            <div class="flex justify-center my-6">
                <span class="px-4 py-1.5 text-sm font-medium text-gray-500 bg-gray-100 rounded-full">
                    <?= $dateLabel ?>
                </span>
            </div>
        <?php endif; ?>

        <?php
            $isOutgoing = $message->is_from_me;
            $time = date('H:i', strtotime($message->created_at));
        ?>
        <div class="flex <?= $isOutgoing ? 'justify-end' : 'justify-start' ?> group/msg"
             x-data="{ showMenu: false }"
             @click.outside="showMenu = false">
            <div class="relative max-w-[85%] lg:max-w-[70%]">
                <!-- Context menu trigger -->
                <button type="button"
                        @click.stop="showMenu = !showMenu"
                        class="absolute <?= $isOutgoing ? 'left-0 -translate-x-full pr-2' : 'right-0 translate-x-full pl-2' ?> top-2 opacity-0 group-hover/msg:opacity-100 transition-opacity text-gray-400 hover:text-gray-600 z-10">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                    </svg>
                </button>
                <!-- Context menu dropdown -->
                <div x-show="showMenu"
                     x-transition
                     class="msg-context-menu <?= $isOutgoing ? 'right-0' : 'left-0' ?>"
                     style="top: 32px;">
                    <button type="button" @click="$dispatch('reply-message', { id: <?= $message->id ?>, name: '<?= $isOutgoing ? 'Вы' : addslashes(Html::encode($message->remote_name ?: 'Контакт')) ?>', text: '<?= addslashes(Html::encode(mb_substr($message->content ?: '[Медиа]', 0, 100))) ?>' }); showMenu = false">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                        Ответить
                    </button>
                    <?php if ($message->message_type === 'text' && $message->content): ?>
                    <button type="button" @click="copyMessageText('<?= addslashes(Html::encode($message->content)) ?>'); showMenu = false">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Копировать
                    </button>
                    <?php endif; ?>
                </div>
                <!-- Message bubble -->
                <div class="px-4 py-3 rounded-2xl shadow-sm text-gray-900 <?= $isOutgoing ? 'bg-[#dcf8c6] rounded-tr-sm' : 'bg-white rounded-tl-sm' ?> msg-bubble <?= $isOutgoing ? 'msg-bubble-out' : 'msg-bubble-in' ?>">
                <?php
                // Render content based on message type
                $downloadUrl = \yii\helpers\Url::to(['download-media', 'message_id' => $message->id]);
                switch ($message->message_type):
                    case 'image':
                        $caption = $message->getInfo('caption') ?? $message->content ?? '';
                        ?>
                        <div class="mb-2 -mx-2 -mt-1">
                            <a href="<?= $downloadUrl ?>" target="_blank" class="block group">
                                <div class="flex items-center justify-center rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors p-6" style="min-height: 120px;">
                                    <div class="text-center">
                                        <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <p class="text-sm mt-2 text-gray-500 group-hover:text-gray-700">Скачать фото</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php if ($caption): ?>
                            <p class="text-base whitespace-pre-wrap break-words leading-relaxed"><?= Html::encode($caption) ?></p>
                        <?php endif;
                        break;

                    case 'video':
                        $caption = $message->getInfo('caption') ?? $message->content ?? '';
                        ?>
                        <div class="mb-2 -mx-2 -mt-1">
                            <a href="<?= $downloadUrl ?>" target="_blank" class="block group">
                                <div class="flex items-center justify-center rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors p-6" style="min-height: 100px;">
                                    <div class="text-center">
                                        <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                        <p class="text-sm mt-2 text-gray-500 group-hover:text-gray-700">Скачать видео</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php if ($caption): ?>
                            <p class="text-base whitespace-pre-wrap break-words leading-relaxed"><?= Html::encode($caption) ?></p>
                        <?php endif;
                        break;

                    case 'audio':
                    case 'ptt':
                        // URL для inline воспроизведения
                        $audioUrl = \yii\helpers\Url::to(['download-media', 'message_id' => $message->id, 'inline' => 1]);
                        $audioId = 'audio-' . $message->id;
                        ?>
                        <div class="wa-audio-player" data-audio-id="<?= $audioId ?>">
                            <audio id="<?= $audioId ?>" preload="none" style="display:none;">
                                <source src="<?= Html::encode($audioUrl) ?>" type="<?= Html::encode($message->media_mimetype ?: 'audio/ogg') ?>">
                            </audio>
                            <button type="button" class="wa-audio-btn" onclick="toggleAudio('<?= $audioId ?>')">
                                <svg id="<?= $audioId ?>-play" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                                <svg id="<?= $audioId ?>-pause" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                                </svg>
                            </button>
                            <div class="wa-audio-progress">
                                <div class="wa-audio-track" onclick="seekAudio(event, '<?= $audioId ?>')">
                                    <div id="<?= $audioId ?>-fill" class="wa-audio-track-fill"></div>
                                </div>
                                <span id="<?= $audioId ?>-time" class="wa-audio-time">0:00</span>
                            </div>
                        </div>
                        <?php
                        break;

                    case 'document':
                        $fileName = $message->media_filename ?: ($message->getInfo('fileName') ?? 'Документ');
                        ?>
                        <a href="<?= $downloadUrl ?>" target="_blank"
                           class="flex items-center gap-3 p-3 -m-1 rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors">
                            <div class="w-12 h-12 flex items-center justify-center bg-blue-100 rounded-lg flex-shrink-0">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="text-base font-medium text-gray-900 truncate block"><?= Html::encode($fileName) ?></span>
                                <span class="text-sm text-gray-500">Нажмите для скачивания</span>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                        </a>
                        <?php
                        break;

                    case 'sticker':
                        ?>
                        <a href="<?= $downloadUrl ?>" target="_blank" class="block">
                            <div class="w-28 h-28 flex items-center justify-center rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors">
                                <span class="text-6xl">🎉</span>
                            </div>
                        </a>
                        <?php
                        break;

                    case 'location':
                        $lat = $message->getInfo('latitude');
                        $lng = $message->getInfo('longitude');
                        if ($lat && $lng): ?>
                            <a href="https://www.google.com/maps?q=<?= $lat ?>,<?= $lng ?>" target="_blank"
                               class="flex items-center gap-3 p-3 -m-1 rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors">
                                <div class="w-10 h-10 flex items-center justify-center bg-red-100 rounded-lg flex-shrink-0">
                                    <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                    </svg>
                                </div>
                                <span class="text-base text-gray-700">Открыть на карте</span>
                            </a>
                        <?php else: ?>
                            <p class="text-base italic text-gray-400">Местоположение</p>
                        <?php endif;
                        break;

                    case 'contact':
                        $contactName = $message->getInfo('displayName') ?? 'Контакт';
                        ?>
                        <div class="flex items-center gap-3 p-2 -m-1">
                            <div class="w-10 h-10 flex items-center justify-center bg-green-100 rounded-full flex-shrink-0">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <span class="text-base font-medium text-gray-900"><?= Html::encode($contactName) ?></span>
                        </div>
                        <?php
                        break;

                    default: // text
                        ?>
                        <p class="text-base whitespace-pre-wrap break-words leading-relaxed"><?= Html::encode($message->content) ?></p>
                        <?php
                        break;
                endswitch;
                ?>

                <!-- Time and status -->
                <div class="flex items-center justify-end gap-1.5 mt-1 -mb-1">
                    <span class="text-[11px] <?= $isOutgoing ? 'text-gray-500' : 'text-gray-400' ?>"><?= $time ?></span>
                    <?php if ($isOutgoing): ?>
                        <?php
                        $checkClass = 'text-gray-400';
                        $isDouble = false;
                        switch ($message->status):
                            case 'read':
                                $checkClass = 'text-blue-500';
                                $isDouble = true;
                                break;
                            case 'delivered':
                                $checkClass = 'text-gray-400';
                                $isDouble = true;
                                break;
                            default: // sent, pending
                                $checkClass = 'text-gray-400';
                                $isDouble = false;
                        endswitch;
                        ?>
                        <?php if ($isDouble): ?>
                            <!-- Двойная галочка -->
                            <svg class="w-4 h-4 <?= $checkClass ?>" viewBox="0 0 20 12" fill="none">
                                <path d="M1 6l3 3 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M7 6l3 3 8-8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        <?php else: ?>
                            <!-- Одинарная галочка -->
                            <svg class="w-4 h-4 <?= $checkClass ?>" viewBox="0 0 16 12" fill="none">
                                <path d="M1 6l4 4 10-9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                </div><!-- /msg-bubble -->
            </div><!-- /relative wrapper -->
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
