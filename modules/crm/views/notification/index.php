<?php
/**
 * Страница всех уведомлений
 *
 * @var yii\web\View $this
 * @var app\models\Notification[] $notifications
 */

use app\helpers\OrganizationUrl;
use app\widgets\tailwind\Icon;
use yii\helpers\Html;

$this->title = 'Уведомления';
$this->params['breadcrumbs'][] = $this->title;

$markReadUrl = OrganizationUrl::to(['notification/mark-read']);
$markAllReadUrl = OrganizationUrl::to(['notification/mark-all-read']);
?>

<div class="space-y-6" x-data="{
        async markAsRead(id, el) {
            try {
                const response = await fetch('<?= $markReadUrl ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': document.querySelector('meta[name=csrf-token]')?.content || ''
                    },
                    body: `id=${id}`
                });
                const data = await response.json();
                if (data.success) {
                    el.classList.remove('bg-primary-50');
                    el.querySelector('.unread-dot')?.remove();
                }
            } catch (e) {
                console.error(e);
            }
        },

        async markAllAsRead() {
            try {
                const response = await fetch('<?= $markAllReadUrl ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': document.querySelector('meta[name=csrf-token]')?.content || ''
                    }
                });
                const data = await response.json();
                if (data.success) {
                    document.querySelectorAll('.notification-item').forEach(el => {
                        el.classList.remove('bg-primary-50');
                        el.querySelector('.unread-dot')?.remove();
                    });
                    if (Alpine.store('toast')) Alpine.store('toast').success('Все уведомления прочитаны');
                }
            } catch (e) {
                console.error(e);
            }
        }
     }">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
            <p class="text-sm text-gray-500 mt-1">История всех уведомлений</p>
        </div>
        <?php if (!empty($notifications)): ?>
            <button @click="markAllAsRead()" class="btn btn-outline btn-sm">
                <?= Icon::show('check', 'sm') ?>
                Прочитать все
            </button>
        <?php endif; ?>
    </div>

    <!-- Notifications List -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm divide-y divide-gray-100">
        <?php if (empty($notifications)): ?>
            <div class="p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                    <?= Icon::show('bell-slash', 'w-8 h-8 text-gray-400') ?>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-1">Нет уведомлений</h3>
                <p class="text-sm text-gray-500">Здесь будут отображаться ваши уведомления</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notif): ?>
                <div class="notification-item flex items-start gap-4 p-4 hover:bg-gray-50 transition-colors <?= !$notif->is_read ? 'bg-primary-50/50' : '' ?>"
                     @click="markAsRead(<?= $notif->id ?>, $el)">
                    <!-- Icon -->
                    <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center
                        <?php
                        switch ($notif->type) {
                            case 1: echo 'bg-blue-100 text-blue-600'; break;
                            case 2: echo 'bg-amber-100 text-amber-600'; break;
                            case 3: echo 'bg-green-100 text-green-600'; break;
                            case 4: echo 'bg-red-100 text-red-600'; break;
                            case 5: echo 'bg-purple-100 text-purple-600'; break;
                            default: echo 'bg-gray-100 text-gray-600';
                        }
                        ?>">
                        <?= Icon::show($notif->getTypeIcon(), 'w-5 h-5') ?>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">
                                    <?= Html::encode($notif->title) ?>
                                </h4>
                                <?php if ($notif->message): ?>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <?= Html::encode($notif->message) ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <?php if (!$notif->is_read): ?>
                                <span class="unread-dot w-2.5 h-2.5 rounded-full bg-primary-500 flex-shrink-0 mt-1"></span>
                            <?php endif; ?>
                        </div>

                        <div class="flex items-center gap-4 mt-2">
                            <span class="text-xs text-gray-400">
                                <?= $notif->getTimeAgo() ?>
                            </span>
                            <?php if ($notif->link): ?>
                                <a href="<?= Html::encode($notif->link) ?>" class="text-xs text-primary-600 hover:underline">
                                    Перейти
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
