<?php

namespace app\widgets\tailwind;

use app\helpers\OrganizationUrl;
use app\models\Pupil;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * PupilTabs Widget - вкладки для карточки ученика
 *
 * Использование:
 * ```php
 * <?= PupilTabs::widget([
 *     'model' => $model,
 *     'activeTab' => 'view', // view, edu, payment
 * ]) ?>
 * ```
 */
class PupilTabs extends Widget
{
    /**
     * @var Pupil модель ученика
     */
    public $model;

    /**
     * @var string активная вкладка: view, edu, payment
     */
    public $activeTab = 'view';

    /**
     * @var array конфигурация вкладок
     */
    protected $tabs = [
        'view' => [
            'label' => 'Основные данные',
            'action' => 'pupil/view',
            'icon' => 'user',
        ],
        'edu' => [
            'label' => 'Обучение',
            'action' => 'pupil/edu',
            'icon' => 'book',
        ],
        'payment' => [
            'label' => 'Оплата',
            'action' => 'pupil/payment',
            'icon' => 'wallet',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $html = '<div class="border-b border-gray-200">';
        $html .= '<nav class="flex gap-4" aria-label="Tabs">';

        foreach ($this->tabs as $key => $tab) {
            $isActive = $key === $this->activeTab;
            $url = OrganizationUrl::to([$tab['action'], 'id' => $this->model->id]);

            $classes = 'px-4 py-2 text-sm font-medium border-b-2 inline-flex items-center gap-2';
            if ($isActive) {
                $classes .= ' border-primary-500 text-primary-600';
            } else {
                $classes .= ' text-gray-500 hover:text-gray-700 hover:border-gray-300 border-transparent';
            }

            $icon = Icon::show($tab['icon'], 'sm');
            $html .= Html::a($icon . ' ' . $tab['label'], $url, ['class' => $classes]);
        }

        $html .= '</nav>';
        $html .= '</div>';

        return $html;
    }
}
