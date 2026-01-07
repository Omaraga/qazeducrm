<?php

namespace app\widgets\tailwind;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use app\helpers\FeatureHelper;
use app\helpers\OrganizationUrl;
use app\services\AddonTrialService;

/**
 * FeatureLock Widget - замок для недоступных функций с возможностью trial
 *
 * Использование:
 * ```php
 * // Проверка доступа к функции
 * <?php if (FeatureLock::isLocked('analytics')): ?>
 *     <?= FeatureLock::widget(['feature' => 'analytics']) ?>
 * <?php else: ?>
 *     ... контент функции ...
 * <?php endif; ?>
 *
 * // Обёртка для контента
 * <?= FeatureLock::wrap('whatsapp', '<a href="...">Отправить WhatsApp</a>') ?>
 *
 * // В меню
 * <?= FeatureLock::menuItem('analytics', 'Аналитика', '/analytics') ?>
 *
 * // Только иконка замка
 * <?= FeatureLock::icon('analytics') ?>
 *
 * // Инлайн бейдж PRO
 * <?= FeatureLock::proBadge() ?>
 * ```
 */
class FeatureLock extends Widget
{
    /**
     * @var string код функции
     */
    public $feature;

    /**
     * @var string контент для показа вместо замка (если функция доступна)
     */
    public $content = '';

    /**
     * @var bool показывать кнопку trial
     */
    public $showTrial = true;

    /**
     * @var bool показывать кнопку покупки
     */
    public $showBuy = true;

    /**
     * @var string размер: xs, sm, md, lg
     */
    public $size = 'md';

    /**
     * @var string вариант отображения: inline, card, tooltip, modal-trigger
     */
    public $variant = 'inline';

    /**
     * @var string текст замка (по умолчанию - название функции)
     */
    public $label;

    /**
     * @var array дополнительные опции
     */
    public $options = [];

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        // Если функция доступна - показываем контент
        if (!self::isLocked($this->feature)) {
            return $this->content;
        }

        return match ($this->variant) {
            'card' => $this->renderCard(),
            'tooltip' => $this->renderTooltip(),
            'modal-trigger' => $this->renderModalTrigger(),
            default => $this->renderInline(),
        };
    }

    /**
     * Проверить, заблокирована ли функция
     */
    public static function isLocked(string $feature): bool
    {
        return !FeatureHelper::has($feature) && !FeatureHelper::hasPlanFeature($feature);
    }

    /**
     * Обернуть контент в проверку доступа
     */
    public static function wrap(string $feature, string $content, array $options = []): string
    {
        if (!self::isLocked($feature)) {
            return $content;
        }

        $widget = new self([
            'feature' => $feature,
            'content' => $content,
            'variant' => $options['variant'] ?? 'tooltip',
            'size' => $options['size'] ?? 'sm',
            'showTrial' => $options['showTrial'] ?? true,
            'showBuy' => $options['showBuy'] ?? true,
        ]);

        return $widget->run();
    }

    /**
     * Элемент меню с проверкой доступа
     */
    public static function menuItem(string $feature, string $label, string $url, array $options = []): string
    {
        $isLocked = self::isLocked($feature);
        $iconClass = $options['icon'] ?? '';
        $class = $options['class'] ?? 'flex items-center gap-2 px-3 py-2 rounded-lg transition';

        if ($isLocked) {
            // Заблокированный пункт меню
            $class .= ' text-gray-400 cursor-not-allowed';

            $html = '<span class="' . $class . '">';
            if ($iconClass) {
                $html .= '<span class="' . $iconClass . '"></span>';
            }
            $html .= Html::encode($label);
            $html .= ' ' . self::icon($feature, ['size' => 'xs']);
            $html .= '</span>';

            return $html;
        }

        // Доступный пункт меню
        $class .= ' text-gray-700 hover:bg-gray-100';
        $html = '<a href="' . $url . '" class="' . $class . '">';
        if ($iconClass) {
            $html .= '<span class="' . $iconClass . '"></span>';
        }
        $html .= Html::encode($label);
        $html .= '</a>';

        return $html;
    }

    /**
     * Иконка замка
     */
    public static function icon(string $feature, array $options = []): string
    {
        if (!self::isLocked($feature)) {
            return '';
        }

        $size = $options['size'] ?? 'sm';
        $sizeClass = match ($size) {
            'xs' => 'w-3 h-3',
            'sm' => 'w-4 h-4',
            'md' => 'w-5 h-5',
            'lg' => 'w-6 h-6',
            default => 'w-4 h-4',
        };

        $class = "inline-block {$sizeClass} text-gray-400";
        if (isset($options['class'])) {
            $class .= ' ' . $options['class'];
        }

        $title = $options['title'] ?? Yii::t('main', 'Функция недоступна в вашем тарифе');

        return '<svg class="' . $class . '" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="' . Html::encode($title) . '">' .
               '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>' .
               '</svg>';
    }

    /**
     * PRO бейдж
     */
    public static function proBadge(array $options = []): string
    {
        $size = $options['size'] ?? 'xs';
        $sizeClass = match ($size) {
            'xs' => 'text-[10px] px-1 py-0.5',
            'sm' => 'text-xs px-1.5 py-0.5',
            'md' => 'text-sm px-2 py-1',
            default => 'text-xs px-1.5 py-0.5',
        };

        $class = "inline-flex items-center font-semibold bg-gradient-to-r from-amber-400 to-amber-500 text-white rounded {$sizeClass}";
        if (isset($options['class'])) {
            $class .= ' ' . $options['class'];
        }

        return '<span class="' . $class . '">PRO</span>';
    }

    /**
     * Рендер инлайн варианта
     */
    protected function renderInline(): string
    {
        $trialInfo = $this->getTrialInfo();

        $sizeClass = match ($this->size) {
            'xs' => 'text-xs gap-1',
            'sm' => 'text-sm gap-1.5',
            'lg' => 'text-base gap-2',
            default => 'text-sm gap-1.5',
        };

        $html = '<span class="inline-flex items-center ' . $sizeClass . ' text-gray-500">';
        $html .= self::icon($this->feature, ['size' => $this->size]);

        if ($this->label) {
            $html .= '<span>' . Html::encode($this->label) . '</span>';
        }

        $html .= self::proBadge(['size' => $this->size]);

        // Кнопка trial
        if ($this->showTrial && $trialInfo['available']) {
            $html .= $this->renderTrialButton();
        }

        $html .= '</span>';

        return $html;
    }

    /**
     * Рендер карточки
     */
    protected function renderCard(): string
    {
        $trialInfo = $this->getTrialInfo();
        $feature = $trialInfo['feature'] ?? null;

        $html = '<div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center">';

        // Иконка
        $html .= '<div class="flex justify-center mb-3">';
        $html .= '<div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center">';
        $html .= self::icon($this->feature, ['size' => 'lg']);
        $html .= '</div>';
        $html .= '</div>';

        // Текст
        $html .= '<h4 class="font-medium text-gray-900 mb-1">';
        $html .= Html::encode($this->label ?: ($feature ? $feature->name : 'Функция недоступна'));
        $html .= ' ' . self::proBadge();
        $html .= '</h4>';

        $html .= '<p class="text-sm text-gray-500 mb-3">';
        $html .= Yii::t('main', 'Эта функция недоступна в вашем тарифе');
        $html .= '</p>';

        // Кнопки
        $html .= '<div class="flex justify-center gap-2">';

        if ($this->showTrial && $trialInfo['available']) {
            $html .= $this->renderTrialButton('button');
        }

        if ($this->showBuy) {
            $html .= '<a href="' . OrganizationUrl::to(['subscription/upgrade']) . '" ';
            $html .= 'class="px-3 py-1.5 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition">';
            $html .= Yii::t('main', 'Подробнее');
            $html .= '</a>';
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Рендер tooltip варианта
     */
    protected function renderTooltip(): string
    {
        $trialInfo = $this->getTrialInfo();

        $html = '<span class="relative inline-flex items-center group cursor-help">';

        // Контент с замком
        $html .= '<span class="text-gray-400 line-through">' . $this->content . '</span>';
        $html .= self::icon($this->feature, ['size' => 'xs', 'class' => 'ml-1']);

        // Tooltip
        $html .= '<span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 ';
        $html .= 'bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 ';
        $html .= 'transition-opacity whitespace-nowrap pointer-events-none z-50">';

        $html .= Yii::t('main', 'Функция недоступна в вашем тарифе');

        if ($trialInfo['available']) {
            $html .= '<br><span class="text-primary-300">';
            $html .= Yii::t('main', 'Попробуйте бесплатно {n} дней', ['n' => $trialInfo['trial_days']]);
            $html .= '</span>';
        }

        $html .= '</span>';
        $html .= '</span>';

        return $html;
    }

    /**
     * Рендер триггера модального окна
     */
    protected function renderModalTrigger(): string
    {
        $trialInfo = $this->getTrialInfo();

        $sizeClass = match ($this->size) {
            'xs' => 'text-xs px-2 py-1',
            'sm' => 'text-sm px-3 py-1.5',
            'lg' => 'text-base px-4 py-2',
            default => 'text-sm px-3 py-1.5',
        };

        $html = '<button type="button" ';
        $html .= 'class="inline-flex items-center gap-1.5 ' . $sizeClass . ' ';
        $html .= 'bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition" ';
        $html .= 'data-toggle="modal" data-target="#trial-modal-' . $this->feature . '" ';
        $html .= 'data-feature="' . Html::encode($this->feature) . '">';

        $html .= self::icon($this->feature, ['size' => $this->size]);
        $html .= '<span>' . Html::encode($this->label ?: Yii::t('main', 'Недоступно')) . '</span>';
        $html .= self::proBadge(['size' => 'xs']);

        $html .= '</button>';

        return $html;
    }

    /**
     * Рендер кнопки trial
     */
    protected function renderTrialButton(string $type = 'link'): string
    {
        $trialInfo = $this->getTrialInfo();

        if (!$trialInfo['available']) {
            return '';
        }

        $url = OrganizationUrl::to(['subscription/start-trial', 'feature' => $this->feature]);
        $days = $trialInfo['trial_days'];

        if ($type === 'button') {
            return '<button type="button" ' .
                   'class="px-3 py-1.5 text-sm bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition" ' .
                   'data-action="start-trial" data-feature="' . Html::encode($this->feature) . '">' .
                   Yii::t('main', 'Попробовать {n} дн.', ['n' => $days]) .
                   '</button>';
        }

        return '<a href="' . $url . '" ' .
               'class="text-primary-600 hover:text-primary-700 text-xs font-medium">' .
               Yii::t('main', 'Попробовать бесплатно →') .
               '</a>';
    }

    /**
     * Получить информацию о trial
     */
    protected function getTrialInfo(): array
    {
        static $cache = [];

        if (isset($cache[$this->feature])) {
            return $cache[$this->feature];
        }

        $trialService = AddonTrialService::forCurrentOrganization();
        if (!$trialService) {
            return $cache[$this->feature] = ['available' => false];
        }

        return $cache[$this->feature] = $trialService->getTrialInfo($this->feature);
    }

    /**
     * Сгенерировать модальное окно для trial (для использования в layout)
     */
    public static function trialModal(string $feature): string
    {
        $trialService = AddonTrialService::forCurrentOrganization();
        if (!$trialService) {
            return '';
        }

        $trialInfo = $trialService->getTrialInfo($feature);
        if (!$trialInfo['available']) {
            return '';
        }

        $featureModel = $trialInfo['feature'];

        ob_start();
        ?>
        <div id="trial-modal-<?= Html::encode($feature) ?>" class="fixed inset-0 z-50 hidden" aria-modal="true">
            <div class="fixed inset-0 bg-black/50" data-dismiss="modal"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 relative">
                    <button type="button" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600" data-dismiss="modal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <div class="text-center">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-primary-100 flex items-center justify-center">
                            <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>

                        <h3 class="text-xl font-semibold text-gray-900 mb-2">
                            <?= Yii::t('main', 'Попробуйте {feature}', ['feature' => Html::encode($featureModel->name)]) ?>
                        </h3>

                        <p class="text-gray-600 mb-4">
                            <?= Html::encode($featureModel->description ?: Yii::t('main', 'Протестируйте функцию бесплатно')) ?>
                        </p>

                        <div class="bg-primary-50 rounded-lg p-4 mb-6">
                            <div class="text-3xl font-bold text-primary-600">
                                <?= $trialInfo['trial_days'] ?> <?= Yii::t('main', 'дней') ?>
                            </div>
                            <div class="text-sm text-primary-700">
                                <?= Yii::t('main', 'бесплатный пробный период') ?>
                            </div>
                        </div>

                        <ul class="text-left text-sm text-gray-600 mb-6 space-y-2">
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-success-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <?= Yii::t('main', 'Без привязки карты') ?>
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-success-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <?= Yii::t('main', 'Полный доступ к функции') ?>
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-success-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <?= Yii::t('main', 'Автоотключение после {n} дней', ['n' => $trialInfo['trial_days']]) ?>
                            </li>
                        </ul>

                        <form action="<?= OrganizationUrl::to(['subscription/start-trial']) ?>" method="post">
                            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                            <?= Html::hiddenInput('feature', $feature) ?>

                            <button type="submit" class="w-full px-4 py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition">
                                <?= Yii::t('main', 'Начать пробный период') ?>
                            </button>
                        </form>

                        <p class="mt-4 text-xs text-gray-500">
                            <?= Yii::t('main', 'После окончания trial: {price}/мес', ['price' => number_format($trialInfo['price_monthly'], 0, '', ' ') . ' KZT']) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
