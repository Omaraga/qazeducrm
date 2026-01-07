<?php

namespace app\widgets\tailwind;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use app\helpers\OrganizationUrl;
use app\services\AddonTrialService;
use app\models\SaasFeature;

/**
 * TrialModal Widget - модальное окно для запуска пробного периода
 *
 * Использование:
 * ```php
 * // В layout или на странице
 * <?= TrialModal::widget(['feature' => 'whatsapp']) ?>
 *
 * // Несколько модальных окон
 * <?= TrialModal::all() ?>
 *
 * // Trigger кнопка
 * <button data-trial-modal="whatsapp">Попробовать</button>
 * ```
 *
 * JavaScript для открытия:
 * ```javascript
 * document.querySelectorAll('[data-trial-modal]').forEach(btn => {
 *     btn.addEventListener('click', () => {
 *         const feature = btn.dataset.trialModal;
 *         document.getElementById('trial-modal-' + feature)?.classList.remove('hidden');
 *     });
 * });
 * ```
 */
class TrialModal extends Widget
{
    /**
     * @var string код функции
     */
    public $feature;

    /**
     * @var bool автоматически показать при загрузке (для определённых условий)
     */
    public $autoShow = false;

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $trialService = AddonTrialService::forCurrentOrganization();
        if (!$trialService) {
            return '';
        }

        $trialInfo = $trialService->getTrialInfo($this->feature);
        if (!$trialInfo['available']) {
            return '';
        }

        return $this->renderModal($trialInfo);
    }

    /**
     * Сгенерировать модальные окна для всех доступных trial
     */
    public static function all(): string
    {
        $trialService = AddonTrialService::forCurrentOrganization();
        if (!$trialService) {
            return '';
        }

        $availableTrials = $trialService->getAvailableTrials();
        $html = '';

        foreach ($availableTrials as $trial) {
            $widget = new self(['feature' => $trial['feature']->code]);
            $html .= $widget->run();
        }

        // Добавляем JS для управления модальными окнами
        $html .= self::renderScript();

        return $html;
    }

    /**
     * Рендер модального окна
     */
    protected function renderModal(array $trialInfo): string
    {
        $feature = $trialInfo['feature'];
        $featureCode = $feature->code;

        ob_start();
        ?>
        <div id="trial-modal-<?= Html::encode($featureCode) ?>"
             class="fixed inset-0 z-50 hidden overflow-y-auto"
             aria-modal="true"
             role="dialog"
             <?= $this->autoShow ? 'data-auto-show="true"' : '' ?>>

            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50 transition-opacity" data-modal-overlay></div>

            <!-- Modal container -->
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">

                    <!-- Close button -->
                    <button type="button"
                            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition"
                            data-modal-close>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <!-- Content -->
                    <div class="p-6">
                        <!-- Icon -->
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center">
                            <?= $this->getFeatureIcon($feature->category) ?>
                        </div>

                        <!-- Title -->
                        <h3 class="text-xl font-bold text-gray-900 text-center mb-2">
                            <?= Yii::t('main', 'Попробуйте {feature}', ['feature' => Html::encode($feature->name)]) ?>
                        </h3>

                        <!-- Description -->
                        <p class="text-gray-600 text-center mb-6">
                            <?= Html::encode($feature->description ?: Yii::t('main', 'Протестируйте функцию бесплатно и оцените её возможности')) ?>
                        </p>

                        <!-- Trial period highlight -->
                        <div class="bg-gradient-to-r from-primary-50 to-primary-100 rounded-xl p-4 mb-6 text-center">
                            <div class="flex items-center justify-center gap-3">
                                <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div class="text-left">
                                    <div class="text-2xl font-bold text-primary-700">
                                        <?= $trialInfo['trial_days'] ?> <?= Yii::t('main', 'дней бесплатно') ?>
                                    </div>
                                    <div class="text-sm text-primary-600">
                                        <?= Yii::t('main', 'Полный доступ без ограничений') ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Benefits list -->
                        <ul class="space-y-3 mb-6">
                            <li class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-success-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-gray-700">
                                    <?= Yii::t('main', 'Без привязки банковской карты') ?>
                                </span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-success-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-gray-700">
                                    <?= Yii::t('main', 'Автоматическое отключение после окончания') ?>
                                </span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-success-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-gray-700">
                                    <?= Yii::t('main', 'Можно отменить в любое время') ?>
                                </span>
                            </li>
                        </ul>

                        <!-- Form -->
                        <form action="<?= OrganizationUrl::to(['subscription/start-trial']) ?>" method="post" class="space-y-4">
                            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                            <?= Html::hiddenInput('feature', $featureCode) ?>

                            <button type="submit"
                                    class="w-full px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 focus:ring-4 focus:ring-primary-200 transition">
                                <?= Yii::t('main', 'Начать бесплатный период') ?>
                            </button>
                        </form>

                        <!-- Price after trial -->
                        <p class="mt-4 text-center text-sm text-gray-500">
                            <?= Yii::t('main', 'После пробного периода: от {price}/мес', [
                                'price' => number_format($trialInfo['price_monthly'], 0, '', ' ') . ' KZT'
                            ]) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Рендер JS для управления модальными окнами
     */
    protected static function renderScript(): string
    {
        return <<<JS
<script>
(function() {
    // Открытие модальных окон
    document.querySelectorAll('[data-trial-modal]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const feature = this.dataset.trialModal;
            const modal = document.getElementById('trial-modal-' + feature);
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    // Закрытие модальных окон
    document.querySelectorAll('[data-modal-close], [data-modal-overlay]').forEach(el => {
        el.addEventListener('click', function() {
            const modal = this.closest('[id^="trial-modal-"]');
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        });
    });

    // Закрытие по Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('[id^="trial-modal-"]:not(.hidden)').forEach(modal => {
                modal.classList.add('hidden');
            });
            document.body.style.overflow = '';
        }
    });

    // Автопоказ
    document.querySelectorAll('[data-auto-show="true"]').forEach(modal => {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    });
})();
</script>
JS;
    }

    /**
     * Получить иконку для категории функции
     */
    protected function getFeatureIcon(string $category): string
    {
        $icons = [
            'integration' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>',
            'analytics' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
            'portal' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>',
            'feature' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>',
        ];

        $path = $icons[$category] ?? $icons['feature'];

        return '<svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">' . $path . '</svg>';
    }
}
