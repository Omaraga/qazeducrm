<?php

namespace app\helpers;

use app\models\Organizations;
use app\models\search\DateSearch;
use yii\bootstrap4\Html;

/**
 * MenuHelper - построение навигационного меню
 *
 * Используется в старом Bootstrap-based layout (views/layouts/header.php)
 * Для нового Tailwind интерфейса используется виджет SidebarMenu
 *
 * @see \app\widgets\tailwind\SidebarMenu
 * @see SettingsHelper для работы с настройками
 */
class MenuHelper
{
    /**
     * Получить URL сайта
     * @deprecated Используйте SettingsHelper::getBaseUrl()
     */
    public static function getUrl(): string
    {
        return SettingsHelper::getBaseUrl();
    }

    /**
     * Получить настройки
     * @deprecated Используйте SettingsHelper::getSettings()
     */
    public static function getSetting()
    {
        return SettingsHelper::getSettings();
    }

    /**
     * Получить логотип
     * @deprecated Используйте SettingsHelper::getLogoHtml()
     */
    public static function getLogo(bool $mini = false): string
    {
        return SettingsHelper::getLogoHtml($mini);
    }

    /**
     * Получить название сайта
     * @deprecated Используйте SettingsHelper::getSiteName()
     */
    public static function getName(): string
    {
        return SettingsHelper::getSiteName();
    }

    /**
     * Нормализовать URL
     * @deprecated Используйте SettingsHelper::normalizeUrl()
     */
    public static function normalizeUrl(?string $url): string
    {
        return SettingsHelper::normalizeUrl($url);
    }

    /**
     * Построить элементы меню для Bootstrap Nav
     *
     * @return array
     */
    public static function getMenuItems(): array
    {
        if (\Yii::$app->user->isGuest) {
            return self::getGuestMenuItems();
        }

        return self::getAuthenticatedMenuItems();
    }

    /**
     * Меню для гостей
     */
    private static function getGuestMenuItems(): array
    {
        return [
            ['label' => 'Login', 'url' => OrganizationUrl::to(['/site/login'])]
        ];
    }

    /**
     * Меню для авторизованных пользователей
     */
    private static function getAuthenticatedMenuItems(): array
    {
        $items = [];
        $user = \Yii::$app->user->identity;
        $organization = Organizations::getCurrentOrganization();
        $controllerId = \Yii::$app->controller->id;

        // Основное меню для администраторов
        if (self::canAccessAdminMenu()) {
            $items = array_merge($items, self::getAdminMenuItems($controllerId));
        }

        // Дополнительные пункты для директора
        if (\Yii::$app->user->can(OrganizationRoles::GENERAL_DIRECTOR)) {
            $items = array_merge($items, self::getDirectorMenuItems($controllerId));
        }

        // Переключение ролей и выход
        $items[] = self::getRoleSwitcherItem($user, $organization);
        $items[] = self::getLogoutItem($user);

        return $items;
    }

    /**
     * Проверить доступ к админ-меню
     */
    private static function canAccessAdminMenu(): bool
    {
        return \Yii::$app->user->can(OrganizationRoles::ADMIN) ||
               \Yii::$app->user->can(OrganizationRoles::DIRECTOR) ||
               \Yii::$app->user->can(OrganizationRoles::GENERAL_DIRECTOR);
    }

    /**
     * Пункты меню администратора
     */
    private static function getAdminMenuItems(string $controllerId): array
    {
        return [
            [
                'label' => 'Ученики',
                'url' => OrganizationUrl::to(['/pupil/index']),
                'active' => $controllerId === 'pupil'
            ],
            [
                'label' => 'Преподаватели',
                'url' => OrganizationUrl::to(['/user/index']),
                'active' => $controllerId === 'user'
            ],
            [
                'label' => 'Группы',
                'url' => OrganizationUrl::to(['/group/index']),
                'active' => $controllerId === 'group'
            ],
            [
                'label' => 'Пробное тестирование',
                'url' => OrganizationUrl::to(['/lids/index']),
                'active' => $controllerId === 'lids'
            ],
            [
                'label' => 'Расписание',
                'items' => [
                    ['label' => 'Расписание', 'url' => OrganizationUrl::to(['/crm/schedule/index'])],
                    ['label' => 'Шаблоны расписания', 'url' => OrganizationUrl::to(['/crm/schedule-template'])],
                ],
                'active' => in_array($controllerId, ['schedule', 'schedule-template'])
            ],
            [
                'label' => 'Отчеты',
                'items' => [
                    ['label' => 'Дневной отчет', 'url' => OrganizationUrl::to(['reports/day'])],
                    ['label' => 'Приход за месяц', 'url' => OrganizationUrl::to(['reports/month', 'type' => DateSearch::TYPE_PAYMENT])],
                    ['label' => 'Оплата и задолженность по ученикам', 'url' => OrganizationUrl::to(['reports/month', 'type' => DateSearch::TYPE_PUPIL_PAYMENT])],
                    ['label' => 'Статистика посещаемости занятий', 'url' => OrganizationUrl::to(['reports/month', 'type' => DateSearch::TYPE_ATTENDANCE])],
                    ['label' => 'Бухгалтерия', 'url' => OrganizationUrl::to(['/payment/index'])]
                ],
                'active' => $controllerId === 'reports'
            ],
        ];
    }

    /**
     * Дополнительные пункты для директора
     */
    private static function getDirectorMenuItems(string $controllerId): array
    {
        return [
            [
                'label' => 'Заработная плата преподавателей',
                'url' => OrganizationUrl::to(['/reports/employer']),
                'active' => $controllerId === 'reports'
            ],
            [
                'label' => 'Справочники',
                'items' => [
                    ['label' => 'Предметы', 'url' => OrganizationUrl::to(['subject/index'])],
                    ['label' => 'Методы оплат', 'url' => OrganizationUrl::to(['pay-method/index'])],
                    ['label' => 'Тарифы', 'url' => OrganizationUrl::to(['/tariff/index'])]
                ],
                'active' => in_array($controllerId, ['subject', 'pay-method', 'tariff'])
            ],
        ];
    }

    /**
     * Переключатель ролей
     */
    private static function getRoleSwitcherItem($user, $organization): array
    {
        $menuRoles = [];
        foreach ($user->rolesMap as $roleId => $name) {
            $menuRoles[] = [
                'label' => $name,
                'url' => OrganizationUrl::to(['site/change-role', 'id' => $roleId])
            ];
        }

        $currentRole = Lists::getRoles()[$user->getCurrentOrganizationRole()] ?? '';
        $orgName = $organization->name ?? '';

        return [
            'label' => "{$currentRole} ({$orgName})",
            'items' => $menuRoles,
            'options' => ['class' => 'ml-sm-4 role-label']
        ];
    }

    /**
     * Кнопка выхода
     */
    private static function getLogoutItem($user): string
    {
        return '<li>'
            . Html::beginForm(['/site/logout'], 'post', ['class' => 'form-inline ml-sm-4'])
            . Html::submitButton(
                '<i class="fa fa-sign-out" aria-hidden="true"></i> Выйти(' . $user->fio . ')',
                ['class' => 'btn btn-link logout']
            )
            . Html::endForm()
            . '</li>';
    }

    /**
     * Проверить активность пункта меню
     *
     * @param string $url URL пункта меню
     * @return bool
     */
    public static function isMenuActive(string $url): bool
    {
        $route = \Yii::$app->controller->getRoute();
        $routeArray = explode('/', $route);
        $urlArray = explode('/', $url);

        return ($routeArray[0] ?? '') === ($urlArray[0] ?? '');
    }
}
