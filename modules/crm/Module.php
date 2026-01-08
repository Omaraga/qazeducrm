<?php

namespace app\modules\crm;

use app\models\Organizations;
use Yii;
use yii\web\ForbiddenHttpException;

/**
 * CRM module - основной функционал CRM системы
 * Доступен только для авторизованных пользователей
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\crm\controllers';

    /**
     * {@inheritdoc}
     */
    public $defaultRoute = 'default';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // Проверка авторизации для всего модуля
        if (Yii::$app->user->isGuest) {
            Yii::$app->response->redirect(['/login'])->send();
            exit;
        }

        // Superadmin без контекста организации -> редирект в админку
        if (Yii::$app->user->can('SUPER')) {
            $orgId = Organizations::getCurrentOrganizationId();
            // Если нет организации в URL и superadmin зашёл напрямую в /crm
            if (!$orgId || $orgId === 0) {
                Yii::$app->response->redirect(['/superadmin'])->send();
                exit;
            }
        }

        // Проверка что у пользователя есть доступ к организации
        $this->checkOrganizationAccess();

        // Устанавливаем layout для модуля CRM
        $this->layout = 'main';
    }

    /**
     * Проверка доступа пользователя к текущей организации
     */
    protected function checkOrganizationAccess()
    {
        // Superadmin имеет доступ ко всем организациям
        if (Yii::$app->user->can('SUPER')) {
            return;
        }

        $orgId = Organizations::getCurrentOrganizationId();
        if (!$orgId) {
            // Нет организации в URL - редирект на выбор
            $user = Yii::$app->user->identity;
            $organizations = $user->userOrganizations ?? [];

            if (!empty($organizations)) {
                $firstOrgId = $organizations[0]->target_id ?? null;
                if ($firstOrgId) {
                    Yii::$app->response->redirect(['/' . $firstOrgId . '/default/index'])->send();
                    exit;
                }
            }

            throw new ForbiddenHttpException('У вас нет доступа к организациям');
        }

        // Проверяем что пользователь имеет роль в этой организации
        $user = Yii::$app->user->identity;
        $hasAccess = false;

        foreach ($user->userOrganizations ?? [] as $userOrg) {
            if ($userOrg->target_id == $orgId) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            throw new ForbiddenHttpException('У вас нет доступа к этой организации');
        }
    }
}
