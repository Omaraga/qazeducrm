<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Tailwind CSS Asset Bundle
 *
 * Этот asset включает скомпилированный Tailwind CSS и Alpine.js
 * для интерактивности без jQuery.
 *
 * Использование:
 *   TailwindAsset::register($this);
 *
 * Для пересборки CSS:
 *   npm run build
 *
 * Для разработки (watch mode):
 *   npm run dev
 */
class TailwindAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
        'css/tailwind.css',
    ];

    public $js = [
        // Toast notifications (загружается перед Alpine.js)
        'js/toast.js',
        // Confirm modal dialogs (загружается перед Alpine.js)
        'js/confirm.js',
        // Form validation (загружается перед Alpine.js)
        'js/validation.js',
        // Loading states
        'js/loading.js',
        // AJAX wrapper with error handling
        'js/ajax.js',
        // Schedule calendar component (загружается перед Alpine.js)
        'js/schedule-calendar.js',
        // Alpine.js Collapse plugin (должен загружаться ДО основного Alpine.js)
        'https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js',
        // Alpine.js для интерактивности (dropdowns, modals, tabs)
        'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js',
        // Chart.js для графиков
        'https://cdn.jsdelivr.net/npm/chart.js',
    ];

    public $jsOptions = [
        'defer' => true,
    ];

    public $depends = [
        // Не зависит от других assets
    ];

    /**
     * Публикуемые файлы (для FontAwesome иконок)
     */
    public $publishOptions = [
        'only' => [
            'css/tailwind.css',
        ],
    ];
}
