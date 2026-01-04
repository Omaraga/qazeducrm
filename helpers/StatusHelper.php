<?php

namespace app\helpers;

use app\models\Lids;
use app\models\Lesson;
use app\models\LessonAttendance;
use app\models\Pupil;
use app\models\SmsLog;
use app\models\TeacherSalary;
use app\models\User;

/**
 * StatusHelper - централизованное управление статусами и их отображением
 *
 * Использование:
 * ```php
 * // Получить цвет статуса
 * $color = StatusHelper::getColor('lids', Lids::STATUS_NEW);
 *
 * // Получить label статуса
 * $label = StatusHelper::getLabel('lids', Lids::STATUS_NEW);
 *
 * // Получить CSS класс для бейджа
 * $class = StatusHelper::getBadgeClass('salary', TeacherSalary::STATUS_PAID);
 * ```
 */
class StatusHelper
{
    /**
     * Цветовые схемы
     */
    const COLOR_PRIMARY = 'primary';
    const COLOR_SUCCESS = 'success';
    const COLOR_WARNING = 'warning';
    const COLOR_DANGER = 'danger';
    const COLOR_INFO = 'info';
    const COLOR_GRAY = 'gray';
    const COLOR_PURPLE = 'purple';
    const COLOR_INDIGO = 'indigo';

    /**
     * Маппинг статусов по моделям
     */
    protected static $statusConfig = [
        // Лиды
        'lids' => [
            Lids::STATUS_NEW => ['label' => 'Новый', 'color' => self::COLOR_PRIMARY, 'icon' => 'star'],
            Lids::STATUS_CONTACTED => ['label' => 'Связались', 'color' => self::COLOR_INFO, 'icon' => 'phone'],
            Lids::STATUS_TRIAL => ['label' => 'Пробное', 'color' => self::COLOR_PURPLE, 'icon' => 'calendar'],
            Lids::STATUS_THINKING => ['label' => 'Думает', 'color' => self::COLOR_WARNING, 'icon' => 'clock'],
            Lids::STATUS_ENROLLED => ['label' => 'Записан', 'color' => self::COLOR_INDIGO, 'icon' => 'check'],
            Lids::STATUS_PAID => ['label' => 'Оплатил', 'color' => self::COLOR_SUCCESS, 'icon' => 'payment'],
            Lids::STATUS_LOST => ['label' => 'Потерян', 'color' => self::COLOR_DANGER, 'icon' => 'x'],
        ],

        // Зарплаты
        'salary' => [
            TeacherSalary::STATUS_DRAFT => ['label' => 'Расчёт', 'color' => self::COLOR_WARNING, 'icon' => 'edit'],
            TeacherSalary::STATUS_APPROVED => ['label' => 'Утверждена', 'color' => self::COLOR_INFO, 'icon' => 'check'],
            TeacherSalary::STATUS_PAID => ['label' => 'Выплачена', 'color' => self::COLOR_SUCCESS, 'icon' => 'payment'],
        ],

        // Уроки
        'lesson' => [
            Lesson::STATUS_PLANED => ['label' => 'Запланирован', 'color' => self::COLOR_INFO, 'icon' => 'calendar'],
            Lesson::STATUS_FINISHED => ['label' => 'Завершён', 'color' => self::COLOR_SUCCESS, 'icon' => 'check'],
            Lesson::STATUS_CANCELED => ['label' => 'Отменён', 'color' => self::COLOR_DANGER, 'icon' => 'x'],
        ],

        // Посещаемость
        'attendance' => [
            LessonAttendance::STATUS_VISIT => ['label' => 'Присутствовал', 'color' => self::COLOR_SUCCESS, 'icon' => 'check'],
            LessonAttendance::STATUS_MISS_WITH_PAY => ['label' => 'Пропуск (с оплатой)', 'color' => self::COLOR_WARNING, 'icon' => 'x'],
            LessonAttendance::STATUS_MISS_WITHOUT_PAY => ['label' => 'Пропуск', 'color' => self::COLOR_DANGER, 'icon' => 'x'],
            LessonAttendance::STATUS_MISS_VALID_REASON => ['label' => 'Уваж. причина', 'color' => self::COLOR_GRAY, 'icon' => 'info'],
        ],

        // SMS
        'sms' => [
            SmsLog::STATUS_PENDING => ['label' => 'В очереди', 'color' => self::COLOR_GRAY, 'icon' => 'clock'],
            SmsLog::STATUS_SENT => ['label' => 'Отправлено', 'color' => self::COLOR_INFO, 'icon' => 'arrow-right'],
            SmsLog::STATUS_DELIVERED => ['label' => 'Доставлено', 'color' => self::COLOR_SUCCESS, 'icon' => 'check'],
            SmsLog::STATUS_FAILED => ['label' => 'Ошибка', 'color' => self::COLOR_DANGER, 'icon' => 'x'],
        ],

        // Ученики
        'pupil' => [
            Pupil::STATUS_ACTIVE => ['label' => 'Активен', 'color' => self::COLOR_SUCCESS, 'icon' => 'check'],
            Pupil::STATUS_ARCHIVED => ['label' => 'Архив', 'color' => self::COLOR_GRAY, 'icon' => 'folder'],
        ],

        // Пользователи
        'user' => [
            User::STATUS_ACTIVE => ['label' => 'Активен', 'color' => self::COLOR_SUCCESS, 'icon' => 'check'],
            User::STATUS_INACTIVE => ['label' => 'Неактивен', 'color' => self::COLOR_WARNING, 'icon' => 'clock'],
            User::STATUS_DELETED => ['label' => 'Удалён', 'color' => self::COLOR_DANGER, 'icon' => 'trash'],
        ],

        // Общие статусы (active/inactive)
        'general' => [
            1 => ['label' => 'Активен', 'color' => self::COLOR_SUCCESS, 'icon' => 'check'],
            0 => ['label' => 'Неактивен', 'color' => self::COLOR_GRAY, 'icon' => 'x'],
        ],

        // Да/Нет
        'boolean' => [
            1 => ['label' => 'Да', 'color' => self::COLOR_SUCCESS, 'icon' => 'check'],
            0 => ['label' => 'Нет', 'color' => self::COLOR_GRAY, 'icon' => 'x'],
            true => ['label' => 'Да', 'color' => self::COLOR_SUCCESS, 'icon' => 'check'],
            false => ['label' => 'Нет', 'color' => self::COLOR_GRAY, 'icon' => 'x'],
        ],
    ];

    /**
     * CSS классы для цветов бейджей
     */
    protected static $badgeClasses = [
        self::COLOR_PRIMARY => 'bg-primary-100 text-primary-800',
        self::COLOR_SUCCESS => 'bg-success-100 text-success-800',
        self::COLOR_WARNING => 'bg-warning-100 text-warning-800',
        self::COLOR_DANGER => 'bg-danger-100 text-danger-800',
        self::COLOR_INFO => 'bg-blue-100 text-blue-800',
        self::COLOR_GRAY => 'bg-gray-100 text-gray-800',
        self::COLOR_PURPLE => 'bg-purple-100 text-purple-800',
        self::COLOR_INDIGO => 'bg-indigo-100 text-indigo-800',
    ];

    /**
     * CSS классы для точек (dots)
     */
    protected static $dotClasses = [
        self::COLOR_PRIMARY => 'bg-primary-500',
        self::COLOR_SUCCESS => 'bg-success-500',
        self::COLOR_WARNING => 'bg-warning-500',
        self::COLOR_DANGER => 'bg-danger-500',
        self::COLOR_INFO => 'bg-blue-500',
        self::COLOR_GRAY => 'bg-gray-500',
        self::COLOR_PURPLE => 'bg-purple-500',
        self::COLOR_INDIGO => 'bg-indigo-500',
    ];

    /**
     * Получить конфигурацию статуса
     */
    public static function getConfig(string $type, $status): ?array
    {
        return self::$statusConfig[$type][$status] ?? null;
    }

    /**
     * Получить label статуса
     */
    public static function getLabel(string $type, $status): string
    {
        $config = self::getConfig($type, $status);
        return $config['label'] ?? 'Неизвестно';
    }

    /**
     * Получить цвет статуса
     */
    public static function getColor(string $type, $status): string
    {
        $config = self::getConfig($type, $status);
        return $config['color'] ?? self::COLOR_GRAY;
    }

    /**
     * Получить иконку статуса
     */
    public static function getIcon(string $type, $status): ?string
    {
        $config = self::getConfig($type, $status);
        return $config['icon'] ?? null;
    }

    /**
     * Получить CSS класс для бейджа
     */
    public static function getBadgeClass(string $type, $status): string
    {
        $color = self::getColor($type, $status);
        return self::$badgeClasses[$color] ?? self::$badgeClasses[self::COLOR_GRAY];
    }

    /**
     * Получить CSS класс для точки
     */
    public static function getDotClass(string $type, $status): string
    {
        $color = self::getColor($type, $status);
        return self::$dotClasses[$color] ?? self::$dotClasses[self::COLOR_GRAY];
    }

    /**
     * Получить все статусы для типа
     */
    public static function getStatuses(string $type): array
    {
        return self::$statusConfig[$type] ?? [];
    }

    /**
     * Получить список статусов для dropdown
     */
    public static function getDropdownList(string $type): array
    {
        $statuses = self::getStatuses($type);
        $list = [];
        foreach ($statuses as $value => $config) {
            $list[$value] = $config['label'];
        }
        return $list;
    }

    /**
     * Проверить, является ли статус "успешным"
     */
    public static function isSuccess(string $type, $status): bool
    {
        return self::getColor($type, $status) === self::COLOR_SUCCESS;
    }

    /**
     * Проверить, является ли статус "опасным"
     */
    public static function isDanger(string $type, $status): bool
    {
        return self::getColor($type, $status) === self::COLOR_DANGER;
    }

    /**
     * Проверить, является ли статус "предупреждающим"
     */
    public static function isWarning(string $type, $status): bool
    {
        return self::getColor($type, $status) === self::COLOR_WARNING;
    }
}
