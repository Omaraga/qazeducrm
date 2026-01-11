<?php

/**
 * Страница отображается когда кабинет отключен для организации
 *
 * @var yii\web\View $this
 * @var app\models\Organizations $organization
 */

use app\modules\cabinet\widgets\Icon;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Кабинет недоступен');
?>

<div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex items-center justify-center gap-4">
                <?php if ($organization->logo): ?>
                    <img src="<?= Html::encode($organization->logo) ?>"
                         alt=""
                         class="h-12 w-12 rounded-xl object-cover">
                <?php else: ?>
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                        <span class="text-white text-xl font-bold"><?= mb_substr($organization->name, 0, 1) ?></span>
                    </div>
                <?php endif; ?>
                <h1 class="text-xl font-bold text-gray-900"><?= Html::encode($organization->name) ?></h1>
            </div>
        </div>
    </header>

    <!-- Content -->
    <main class="flex-1 flex items-center justify-center p-4">
        <div class="max-w-md w-full text-center">
            <div class="card-glass-solid p-8">
                <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gray-100 flex items-center justify-center">
                    <?= Icon::show('lock-closed', 'xl', 'text-gray-400') ?>
                </div>

                <h2 class="text-xl font-bold text-gray-900 mb-2">
                    <?= Yii::t('app', 'Личный кабинет недоступен') ?>
                </h2>

                <p class="text-gray-500 mb-6">
                    <?= Yii::t('app', 'К сожалению, личный кабинет временно отключен для этой организации. Пожалуйста, свяжитесь с администрацией для получения информации.') ?>
                </p>

                <?php if ($organization->phone): ?>
                    <a href="tel:<?= Html::encode(preg_replace('/[^+0-9]/', '', $organization->phone)) ?>"
                       class="btn-ios-primary w-full justify-center">
                        <?= Icon::show('phone', 'sm') ?>
                        <?= Html::encode($organization->phone) ?>
                    </a>
                <?php endif; ?>

                <!-- Social links -->
                <?php
                $instagram = $organization->instagram ?? null;
                $whatsapp = $organization->whatsapp ?? null;
                $telegram = $organization->telegram ?? null;
                $hasSocial = $instagram || $whatsapp || $telegram;
                ?>
                <?php if ($hasSocial): ?>
                    <div class="flex items-center justify-center gap-3 mt-4">
                        <?php if ($whatsapp): ?>
                            <a href="https://wa.me/<?= Html::encode(preg_replace('/[^0-9]/', '', $whatsapp)) ?>"
                               target="_blank"
                               class="w-12 h-12 rounded-full bg-green-500 flex items-center justify-center text-white hover:scale-110 transition-transform shadow-md">
                                <?= Icon::show('whatsapp', 'md') ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($telegram): ?>
                            <a href="https://t.me/<?= Html::encode(ltrim($telegram, '@')) ?>"
                               target="_blank"
                               class="w-12 h-12 rounded-full bg-sky-500 flex items-center justify-center text-white hover:scale-110 transition-transform shadow-md">
                                <?= Icon::show('telegram', 'md') ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($instagram): ?>
                            <a href="https://instagram.com/<?= Html::encode(ltrim($instagram, '@')) ?>"
                               target="_blank"
                               class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500 via-pink-500 to-orange-400 flex items-center justify-center text-white hover:scale-110 transition-transform shadow-md">
                                <?= Icon::show('instagram', 'md') ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-4">
        <p class="text-center text-sm text-gray-400">
            &copy; <?= date('Y') ?> <?= Html::encode($organization->name) ?>
        </p>
    </footer>
</div>
