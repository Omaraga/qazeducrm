<?php

/**
 * @var yii\web\View $this
 * @var app\models\Organizations $organization
 * @var string $phone
 */

use app\modules\cabinet\widgets\Icon;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Подключите Telegram');

$botUsername = Yii::$app->params['telegramBot']['username'] ?? '';
?>

<div class="flex justify-center">
    <div class="w-full max-w-md space-y-4">
        <!-- Основная карточка -->
        <div class="card-glass-solid overflow-hidden">
            <div class="p-6 sm:p-8 text-center">
                <!-- Иконка Telegram -->
                <div class="w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-blue-500" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                    </svg>
                </div>

                <h2 class="text-xl font-bold text-gray-900 mb-3">
                    <?= Yii::t('app', 'Подключите Telegram') ?>
                </h2>

                <p class="text-gray-500 mb-6 text-sm leading-relaxed">
                    <?= Yii::t('app', 'Для получения кодов входа добавьте нашего бота и отправьте свой контакт') ?>
                </p>

                <?php if ($botUsername): ?>
                    <!-- Кнопка открытия бота -->
                    <a href="https://t.me/<?= Html::encode($botUsername) ?>"
                       target="_blank"
                       class="btn-ios-primary w-full mb-4 inline-flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                        </svg>
                        <?= Yii::t('app', 'Открыть Telegram') ?>
                    </a>
                <?php else: ?>
                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-xl mb-4">
                        <p class="text-sm text-yellow-800">
                            <?= Yii::t('app', 'Telegram бот не настроен. Обратитесь к администратору.') ?>
                        </p>
                    </div>
                <?php endif; ?>

                <!-- Разделитель -->
                <div class="border-t pt-4 mt-4">
                    <p class="text-sm text-gray-500 mb-3">
                        <?= Yii::t('app', 'После привязки нажмите:') ?>
                    </p>
                    <button id="retry-btn"
                            class="btn-ios-secondary w-full inline-flex items-center justify-center gap-2 opacity-50 cursor-not-allowed"
                            disabled>
                        <?= Icon::show('arrow-path', 'sm') ?>
                        <span id="btn-text"><?= Yii::t('app', 'Ожидание привязки...') ?></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Инструкция -->
        <div class="card-glass-solid overflow-hidden">
            <div class="p-5">
                <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900 mb-4">
                    <?= Icon::show('information-circle', 'sm', 'text-indigo-500') ?>
                    <?= Yii::t('app', 'Как подключить:') ?>
                </h3>
                <ol class="space-y-3">
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs font-bold flex items-center justify-center">1</span>
                        <span class="text-sm text-gray-600 pt-0.5">
                            <?= Yii::t('app', 'Откройте бота') ?>
                            <?php if ($botUsername): ?>
                                <span class="font-mono text-indigo-600">@<?= Html::encode($botUsername) ?></span>
                            <?php endif; ?>
                        </span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs font-bold flex items-center justify-center">2</span>
                        <span class="text-sm text-gray-600 pt-0.5"><?= Yii::t('app', 'Нажмите Start или отправьте любое сообщение') ?></span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs font-bold flex items-center justify-center">3</span>
                        <span class="text-sm text-gray-600 pt-0.5"><?= Yii::t('app', 'Нажмите кнопку "Поделиться контактом"') ?></span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs font-bold flex items-center justify-center">4</span>
                        <span class="text-sm text-gray-600 pt-0.5"><?= Yii::t('app', 'Вернитесь сюда и нажмите "Получить код"') ?></span>
                    </li>
                </ol>
            </div>
        </div>

        <!-- Кнопка назад -->
        <div class="text-center">
            <a href="<?= Url::to(['/cabinet/default/login', 'org' => $organization->id]) ?>"
               class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition-colors">
                <?= Icon::show('arrow-left', 'sm') ?>
                <?= Yii::t('app', 'Вернуться') ?>
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const retryBtn = document.getElementById('retry-btn');
    const btnText = document.getElementById('btn-text');
    const checkUrl = '<?= Url::to(['/cabinet/default/check-linked', 'org' => $organization->id]) ?>';
    const loginUrl = '<?= Url::to(['/cabinet/default/login', 'org' => $organization->id]) ?>';

    let checkCount = 0;
    const maxChecks = 120; // 6 минут максимум (каждые 3 секунды)

    // Проверяем привязку каждые 3 секунды
    const checkInterval = setInterval(async function() {
        checkCount++;

        if (checkCount > maxChecks) {
            clearInterval(checkInterval);
            btnText.textContent = '<?= Yii::t('app', 'Время истекло. Обновите страницу.') ?>';
            return;
        }

        try {
            const response = await fetch(checkUrl);
            const data = await response.json();

            if (data.linked) {
                clearInterval(checkInterval);
                retryBtn.disabled = false;
                retryBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                btnText.textContent = '<?= Yii::t('app', 'Получить код') ?>';
            }
        } catch (e) {
            console.error('Check linked error:', e);
        }
    }, 3000);

    // Обработка клика по кнопке
    retryBtn.addEventListener('click', function() {
        if (!retryBtn.disabled) {
            window.location.href = loginUrl;
        }
    });
});
</script>
