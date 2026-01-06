<?php

namespace app\helpers;

use app\models\Settings;
use Yii;

/**
 * SettingsHelper - работа с настройками системы
 *
 * Централизованный доступ к настройкам из модели Settings
 * и параметров приложения (Yii::$app->params)
 */
class SettingsHelper
{
    /**
     * @var Settings|null Кэш настроек
     */
    private static ?Settings $_settings = null;

    /**
     * Получить модель настроек
     *
     * @param bool $refresh Принудительно обновить кэш
     * @return Settings
     */
    public static function getSettings(bool $refresh = false): Settings
    {
        if (self::$_settings === null || $refresh) {
            self::$_settings = Settings::find()->one() ?: new Settings();
        }
        return self::$_settings;
    }

    /**
     * Сбросить кэш настроек
     */
    public static function clearCache(): void
    {
        self::$_settings = null;
    }

    /**
     * Получить базовый URL сайта
     *
     * @return string
     */
    public static function getBaseUrl(): string
    {
        $settings = self::getSettings();
        if ($settings->url) {
            return $settings->url;
        }
        return Yii::$app->params['url'] ?? '';
    }

    /**
     * Получить название сайта/организации
     *
     * @return string
     */
    public static function getSiteName(): string
    {
        $settings = self::getSettings();
        return $settings->name ?: 'Сайт';
    }

    /**
     * Получить URL логотипа
     *
     * @param bool $mini Миниатюрная версия
     * @return string
     */
    public static function getLogoUrl(bool $mini = false): string
    {
        $settings = self::getSettings();

        if ($settings->logo) {
            return self::getBaseUrl() . $settings->logo;
        }

        return $mini ? '/images/logo_star_mini.jpg' : '/images/logo_star_black.png';
    }

    /**
     * Получить HTML логотипа
     *
     * @param bool $mini Миниатюрная версия
     * @return string
     */
    public static function getLogoHtml(bool $mini = false): string
    {
        $url = self::getLogoUrl($mini);

        if ($mini) {
            return '<img src="' . $url . '" style="max-width:50px" alt="Logo"/>';
        }

        return '<img src="' . $url . '" style="max-width:100px;float:left;margin-left:10px;" alt="Logo"/>';
    }

    /**
     * Получить значение настройки по ключу
     *
     * @param string $key Ключ настройки
     * @param mixed $default Значение по умолчанию
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $settings = self::getSettings();

        if (property_exists($settings, $key)) {
            return $settings->$key ?? $default;
        }

        // Проверяем в params приложения
        return Yii::$app->params[$key] ?? $default;
    }

    /**
     * Проверить, включена ли функция
     *
     * @param string $feature Название функции
     * @return bool
     */
    public static function isFeatureEnabled(string $feature): bool
    {
        return (bool) self::get($feature, false);
    }

    /**
     * Нормализовать URL (добавить / в начало если нужно)
     *
     * @param string|null $url
     * @return string
     */
    public static function normalizeUrl(?string $url): string
    {
        if (!$url || strlen($url) === 0) {
            return '#';
        }

        if ($url[0] === '/' || str_contains($url, 'http')) {
            return $url;
        }

        return '/' . $url;
    }
}
