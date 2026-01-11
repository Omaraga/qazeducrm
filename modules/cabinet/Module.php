<?php

namespace app\modules\cabinet;

use app\models\Organizations;
use Yii;

/**
 * Cabinet module - личный кабинет родителя/ученика
 * Доступен для авторизованных родителей по телефону + SMS-код
 *
 * URL структура: /cabinet/{org_id}/... для брендированного доступа
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\cabinet\controllers';

    /**
     * {@inheritdoc}
     */
    public $defaultRoute = 'default';

    /**
     * @var Organizations|null Текущая организация для брендирования
     */
    public static $currentOrganization = null;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // Устанавливаем layout для модуля Cabinet
        $this->layout = 'main';

        // Загружаем организацию из URL если есть
        $orgId = Yii::$app->request->get('org');
        if ($orgId) {
            self::$currentOrganization = Organizations::findOne([
                'id' => $orgId,
                'is_deleted' => 0,
                'status' => Organizations::STATUS_ACTIVE,
            ]);
        }
    }

    /**
     * Получить текущую организацию (из URL или сессии)
     */
    public static function getCurrentOrganization()
    {
        if (self::$currentOrganization) {
            return self::$currentOrganization;
        }

        $orgId = self::getOrganizationId();
        if ($orgId) {
            self::$currentOrganization = Organizations::findOne([
                'id' => $orgId,
                'is_deleted' => 0,
            ]);
        }

        return self::$currentOrganization;
    }

    /**
     * Проверка авторизации родителя
     * Вызывается из контроллеров
     */
    public static function checkParentAuth()
    {
        $session = Yii::$app->session;

        // Проверяем сессию родителя
        if (!$session->has('cabinet_parent_id') || !$session->has('cabinet_pupil_ids')) {
            return false;
        }

        return true;
    }

    /**
     * Получить ID текущего родителя из сессии
     */
    public static function getParentId()
    {
        return Yii::$app->session->get('cabinet_parent_id');
    }

    /**
     * Получить IDs учеников родителя
     */
    public static function getPupilIds()
    {
        return Yii::$app->session->get('cabinet_pupil_ids', []);
    }

    /**
     * Получить ID организации
     */
    public static function getOrganizationId()
    {
        return Yii::$app->session->get('cabinet_organization_id');
    }

    /**
     * Установить данные авторизации в сессию
     */
    public static function setAuthData($parentId, $pupilIds, $organizationId, $parentPhone)
    {
        $session = Yii::$app->session;
        $session->set('cabinet_parent_id', $parentId);
        $session->set('cabinet_pupil_ids', $pupilIds);
        $session->set('cabinet_organization_id', $organizationId);
        $session->set('cabinet_parent_phone', $parentPhone);
    }

    /**
     * Очистить данные авторизации
     */
    public static function logout()
    {
        $session = Yii::$app->session;
        $session->remove('cabinet_parent_id');
        $session->remove('cabinet_pupil_ids');
        $session->remove('cabinet_organization_id');
        $session->remove('cabinet_parent_phone');
        $session->remove('cabinet_sms_code');
        $session->remove('cabinet_sms_code_time');
    }
}
