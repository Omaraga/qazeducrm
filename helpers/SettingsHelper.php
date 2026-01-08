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

        // Проверяем AR атрибуты (колонки БД)
        if ($settings->hasAttribute($key)) {
            return $settings->getAttribute($key) ?? $default;
        }

        // Проверяем виртуальные свойства (из info JSON)
        if ($settings->canGetProperty($key)) {
            try {
                return $settings->$key ?? $default;
            } catch (\Exception $e) {
                // Ignore
            }
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

    // ==================== КОНТАКТЫ ====================

    /**
     * Получить основной телефон
     */
    public static function getMainPhone(): string
    {
        $settings = self::getSettings();
        return $settings->getMainPhone() ?: '+7 700 123 45 67';
    }

    /**
     * Получить email
     */
    public static function getEmail(): string
    {
        return self::get('email', 'info@qazaqedu.kz');
    }

    /**
     * Получить адрес
     */
    public static function getAddress(): string
    {
        $settings = self::getSettings();
        return $settings->address ?: 'г. Алматы';
    }

    /**
     * Получить часы работы
     */
    public static function getWorkingHours(): string
    {
        return self::get('working_hours', 'Пн-Пт: 9:00 - 18:00, Сб-Вс: выходной');
    }

    // ==================== СОЦСЕТИ ====================

    /**
     * Получить ссылку Telegram
     */
    public static function getTelegram(): string
    {
        $value = self::get('telegram', '');
        if (empty($value)) return '#';

        // Если это username (@name), преобразуем в ссылку
        if (str_starts_with($value, '@')) {
            return 'https://t.me/' . ltrim($value, '@');
        }
        return $value;
    }

    /**
     * Получить username Telegram для отображения
     */
    public static function getTelegramUsername(): string
    {
        return self::get('telegram', '@qazaqedu_support');
    }

    /**
     * Получить ссылку YouTube
     */
    public static function getYoutube(): string
    {
        return self::get('youtube', '') ?: '#';
    }

    /**
     * Получить все соцсети
     */
    public static function getSocialLinks(): array
    {
        $settings = self::getSettings();
        return [
            'telegram' => self::getTelegram(),
            'instagram' => self::getSafeAttribute($settings, 'instagram', '#'),
            'whatsapp' => self::getSafeAttribute($settings, 'whatsapp', '#'),
            'youtube' => self::getYoutube(),
            'facebook' => self::getSafeAttribute($settings, 'facebook', '#'),
        ];
    }

    /**
     * Безопасно получить атрибут модели
     */
    private static function getSafeAttribute(Settings $model, string $attribute, $default = null)
    {
        try {
            if ($model->hasAttribute($attribute)) {
                return $model->getAttribute($attribute) ?: $default;
            }
            // Для виртуальных атрибутов из info
            if ($model->canGetProperty($attribute)) {
                return $model->$attribute ?: $default;
            }
        } catch (\Exception $e) {
            // Если атрибут недоступен, возвращаем default
        }
        return $default;
    }

    // ==================== СТАТИСТИКА ЛЕНДИНГА ====================

    /**
     * Получить статистику для лендинга
     * @return array [['value' => '200+', 'label' => 'Учебных центров'], ...]
     */
    public static function getLandingStats(): array
    {
        $settings = self::getSettings();
        return [
            [
                'value' => $settings->stat_centers_count ?: '200+',
                'label' => $settings->stat_centers_label ?: 'Учебных центров',
            ],
            [
                'value' => $settings->stat_pupils_count ?: '15K+',
                'label' => $settings->stat_pupils_label ?: 'Учеников',
            ],
            [
                'value' => $settings->stat_satisfaction_count ?: '99%',
                'label' => $settings->stat_satisfaction_label ?: 'Довольных клиентов',
            ],
        ];
    }

    // ==================== SEO ====================

    /**
     * Получить Meta Title
     */
    public static function getMetaTitle(): string
    {
        return self::get('meta_title', 'Qazaq Education CRM - Система управления учебным центром');
    }

    /**
     * Получить Meta Description
     */
    public static function getMetaDescription(): string
    {
        return self::get('meta_description', 'Современная CRM система для учебных центров. Учет учеников, расписание, финансы, отчёты.');
    }

    /**
     * Получить Meta Keywords
     */
    public static function getMetaKeywords(): string
    {
        return self::get('meta_keywords', 'CRM, учебный центр, ученики, расписание, финансы');
    }

    // ==================== FEATURES PAGE ====================

    /**
     * Получить данные для страницы возможностей
     */
    public static function getFeatures(): array
    {
        $settings = self::getSettings();
        return [
            'hero_title' => $settings->features_hero_title ?: 'Все возможности системы',
            'hero_subtitle' => $settings->features_hero_subtitle ?: 'Полный набор инструментов для управления учебным центром',
            'features' => [
                [
                    'title' => $settings->feature_1_title ?: 'Полный контроль над базой учеников',
                    'description' => $settings->feature_1_description ?: 'Ведите подробный учёт всех учеников с историей обучения, посещаемости и платежей.',
                ],
                [
                    'title' => $settings->feature_2_title ?: 'Гибкое управление группами',
                    'description' => $settings->feature_2_description ?: 'Создавайте группы любой структуры, назначайте преподавателей и формируйте расписание.',
                ],
                [
                    'title' => $settings->feature_3_title ?: 'Прозрачный учёт финансов',
                    'description' => $settings->feature_3_description ?: 'Контролируйте все платежи, расходы и доходы в одном месте.',
                ],
                [
                    'title' => $settings->feature_4_title ?: 'Принимайте решения на основе данных',
                    'description' => $settings->feature_4_description ?: 'Получайте детальную аналитику и отчёты для принятия обоснованных решений.',
                ],
            ],
        ];
    }
}
