<?php

/** @var yii\web\View $this */
/** @var app\modules\cabinet\models\LoginForm $model */
/** @var int $org */

use app\modules\cabinet\widgets\Icon;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = Yii::t('app', 'Подтверждение');
?>

<div class="flex justify-center">
    <div class="w-full max-w-md">
        <div class="card-glass-solid overflow-hidden">
            <div class="p-6 sm:p-8">
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="w-20 h-20 rounded-[22px] bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center mx-auto mb-5 shadow-lg">
                        <svg class="w-10 h-10 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900"><?= Yii::t('app', 'Введите код') ?></h1>
                    <p class="text-gray-500 mt-2 text-sm leading-relaxed">
                        <?= Yii::t('app', 'Мы отправили код в Telegram') ?>
                    </p>
                </div>

                <!-- Form -->
                <?php $form = ActiveForm::begin([
                    'id' => 'verify-form',
                ]); ?>

                <!-- Code Input - 6 digits -->
                <div class="mb-6">
                    <div class="flex justify-center gap-2" x-data="codeInput()">
                        <?php for ($i = 0; $i < 6; $i++): ?>
                            <input type="text"
                                   class="w-11 h-14 text-center text-xl font-bold rounded-xl border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all bg-gray-50 focus:bg-white"
                                   maxlength="1"
                                   inputmode="numeric"
                                   pattern="[0-9]"
                                   autocomplete="one-time-code"
                                   x-ref="digit<?= $i ?>"
                                   @input="handleInput($event, <?= $i ?>)"
                                   @keydown="handleKeydown($event, <?= $i ?>)"
                                   @paste="handlePaste($event)">
                        <?php endfor; ?>
                    </div>

                    <!-- Hidden actual input for form submission -->
                    <?= $form->field($model, 'code', [
                        'template' => "{input}\n{error}",
                        'inputOptions' => [
                            'class' => 'sr-only',
                            'id' => 'loginform-code',
                            'x-ref' => 'hiddenCode',
                        ],
                        'errorOptions' => ['class' => 'mt-3 text-sm text-red-600 text-center'],
                    ])->label(false) ?>
                </div>

                <button type="submit" class="btn-ios-primary w-full text-base">
                    <?= Icon::show('check', 'sm') ?>
                    <?= Yii::t('app', 'Войти') ?>
                </button>

                <?php ActiveForm::end(); ?>

                <!-- Resend Code -->
                <div class="text-center mt-8 pt-6 border-t border-gray-100">
                    <p class="text-sm text-gray-500 mb-4">
                        <?= Yii::t('app', 'Не получили код?') ?>
                    </p>
                    <a href="<?= Url::to(['/cabinet/default/resend-code', 'org' => $org]) ?>"
                       class="btn-ios-ghost">
                        <?= Icon::show('arrow-path', 'sm') ?>
                        <?= Yii::t('app', 'Отправить повторно') ?>
                    </a>
                </div>

                <!-- Back Link -->
                <div class="text-center mt-4">
                    <a href="<?= Url::to(['/cabinet/default/login', 'org' => $org]) ?>"
                       class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition-colors touch-target justify-center">
                        <?= Icon::show('arrow-left', 'sm') ?>
                        <?= Yii::t('app', 'Вернуться') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<JS
function codeInput() {
    return {
        handleInput(e, index) {
            const value = e.target.value.replace(/[^0-9]/g, '');
            e.target.value = value;

            if (value && index < 5) {
                this.\$refs['digit' + (index + 1)].focus();
            }

            this.updateHiddenInput();
        },
        handleKeydown(e, index) {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                this.\$refs['digit' + (index - 1)].focus();
            }
        },
        handlePaste(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            const digits = paste.replace(/[^0-9]/g, '').slice(0, 6);

            for (let i = 0; i < digits.length; i++) {
                if (this.\$refs['digit' + i]) {
                    this.\$refs['digit' + i].value = digits[i];
                }
            }

            if (digits.length === 6) {
                this.\$refs['digit5'].focus();
            }

            this.updateHiddenInput();
        },
        updateHiddenInput() {
            let code = '';
            for (let i = 0; i < 6; i++) {
                code += this.\$refs['digit' + i].value || '';
            }
            this.\$refs.hiddenCode.value = code;

            // Auto-submit when all 6 digits entered
            if (code.length === 6) {
                setTimeout(() => {
                    document.getElementById('verify-form').submit();
                }, 200);
            }
        }
    };
}
JS;
$this->registerJs($js, \yii\web\View::POS_HEAD);
?>
