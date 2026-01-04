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
