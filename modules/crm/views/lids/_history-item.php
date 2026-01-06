<?php

use app\models\LidHistory;
use app\widgets\tailwind\Icon;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\LidHistory $item */

// Иконки и цвета для разных типов
$typeConfig = [
    LidHistory::TYPE_CREATED => [
        'icon' => 'plus-circle',
        'color' => 'primary',
        'bg' => 'bg-primary-100',
        'text' => 'text-primary-600',
        'label' => 'Лид создан',
    ],
    LidHistory::TYPE_CALL => [
        'icon' => 'phone',
        'color' => 'green',
        'bg' => 'bg-green-100',
        'text' => 'text-green-600',
        'label' => 'Звонок',
    ],
    LidHistory::TYPE_WHATSAPP => [
        'icon' => 'whatsapp',
        'color' => 'green',
        'bg' => 'bg-green-100',
        'text' => 'text-green-500',
        'label' => 'WhatsApp',
    ],
    LidHistory::TYPE_MESSAGE => [
        'icon' => 'chat-bubble-left',
        'color' => 'blue',
        'bg' => 'bg-blue-100',
        'text' => 'text-blue-600',
        'label' => 'Сообщение',
    ],
    LidHistory::TYPE_NOTE => [
        'icon' => 'document-text',
        'color' => 'gray',
        'bg' => 'bg-gray-100',
        'text' => 'text-gray-600',
        'label' => 'Заметка',
    ],
    LidHistory::TYPE_STATUS_CHANGE => [
        'icon' => 'arrow-path',
        'color' => 'purple',
        'bg' => 'bg-purple-100',
        'text' => 'text-purple-600',
        'label' => 'Смена статуса',
    ],
    LidHistory::TYPE_MEETING => [
        'icon' => 'user-group',
        'color' => 'indigo',
        'bg' => 'bg-indigo-100',
        'text' => 'text-indigo-600',
        'label' => 'Встреча',
    ],
    LidHistory::TYPE_CONVERTED => [
        'icon' => 'check-circle',
        'color' => 'success',
        'bg' => 'bg-success-100',
        'text' => 'text-success-600',
        'label' => 'Конвертирован',
    ],
];

$config = $typeConfig[$item->type] ?? [
    'icon' => 'information-circle',
    'color' => 'gray',
    'bg' => 'bg-gray-100',
    'text' => 'text-gray-600',
    'label' => 'Событие',
];

$statusLabels = \app\models\Lids::getStatusList();
?>

<div class="relative pl-10">
    <!-- Icon -->
    <div class="absolute left-0 w-8 h-8 rounded-full <?= $config['bg'] ?> flex items-center justify-center <?= $config['text'] ?>">
        <?php if ($item->type === LidHistory::TYPE_WHATSAPP): ?>
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
        <?php else: ?>
            <?= Icon::show($config['icon'], 'sm') ?>
        <?php endif; ?>
    </div>

    <!-- Content -->
    <div class="bg-white rounded-lg border border-gray-200 p-3">
        <!-- Header -->
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium <?= $config['text'] ?>"><?= Html::encode($config['label']) ?></span>
                <?php if ($item->call_duration): ?>
                    <span class="text-xs text-gray-400">
                        <?= Icon::show('clock', 'xs', 'inline') ?>
                        <?= $item->getFormattedCallDuration() ?>
                    </span>
                <?php endif; ?>
            </div>
            <span class="text-xs text-gray-400"><?= $item->getFormattedDate() ?></span>
        </div>

        <!-- Status Change -->
        <?php if ($item->type === LidHistory::TYPE_STATUS_CHANGE && $item->status_from !== null && $item->status_to !== null): ?>
            <div class="flex items-center gap-2 mb-2 text-sm">
                <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-600">
                    <?= Html::encode($statusLabels[$item->status_from] ?? 'Неизвестно') ?>
                </span>
                <?= Icon::show('arrow-right', 'sm', 'text-gray-400') ?>
                <span class="px-2 py-0.5 rounded bg-primary-100 text-primary-700">
                    <?= Html::encode($statusLabels[$item->status_to] ?? 'Неизвестно') ?>
                </span>
            </div>
        <?php endif; ?>

        <!-- Comment -->
        <?php if ($item->comment): ?>
            <p class="text-sm text-gray-700 whitespace-pre-wrap"><?= Html::encode($item->comment) ?></p>
        <?php endif; ?>

        <!-- Next Contact -->
        <?php if ($item->next_contact_date): ?>
            <div class="mt-2 flex items-center gap-1 text-xs text-gray-500">
                <?= Icon::show('calendar', 'xs', 'inline') ?>
                <span>След. контакт: <?= date('d.m.Y', strtotime($item->next_contact_date)) ?></span>
            </div>
        <?php endif; ?>

        <!-- User -->
        <?php if ($item->user): ?>
            <div class="mt-2 flex items-center gap-2 text-xs text-gray-400">
                <div class="w-5 h-5 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-[10px] font-medium">
                    <?= mb_substr($item->user->fio, 0, 1) ?>
                </div>
                <span><?= Html::encode($item->user->fio) ?></span>
            </div>
        <?php endif; ?>
    </div>
</div>
