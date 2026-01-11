<?php

namespace app\modules\cabinet\controllers;

use app\models\Pupil;
use app\models\relations\EducationGroup;
use app\modules\cabinet\Module;
use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

/**
 * Базовый контроллер для модуля Cabinet
 * Проверяет авторизацию родителя
 */
class CabinetBaseController extends Controller
{
    /**
     * @var Pupil[] Ученики текущего родителя
     */
    protected $_pupils;

    /**
     * @var int ID организации
     */
    protected $_organizationId;

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        // Разрешаем доступ к странице авторизации без проверки
        if ($this->id === 'default' && in_array($action->id, ['login', 'verify', 'logout', 'select-organization', 'resend-code'])) {
            return parent::beforeAction($action);
        }

        // Получаем org из URL
        $orgFromUrl = Yii::$app->request->get('org');

        // Проверяем авторизацию родителя
        if (!Module::checkParentAuth()) {
            if ($orgFromUrl) {
                return $this->redirect(['/cabinet/default/login', 'org' => $orgFromUrl]);
            }
            return $this->redirect(['/cabinet/default/select-organization']);
        }

        // Проверяем что пользователь авторизован в правильной организации
        $this->_organizationId = Module::getOrganizationId();
        if ($orgFromUrl && $this->_organizationId != $orgFromUrl) {
            // Пользователь пытается зайти в другую организацию - показываем логин
            return $this->redirect(['/cabinet/default/login', 'org' => $orgFromUrl]);
        }

        // Загружаем учеников
        $pupilIds = Module::getPupilIds();

        $this->_pupils = Pupil::find()
            ->where(['id' => $pupilIds])
            ->andWhere(['is_deleted' => 0])
            ->all();

        if (empty($this->_pupils)) {
            Module::logout();
            Yii::$app->session->setFlash('error', Yii::t('app', 'Ученики не найдены'));
            if ($this->_organizationId) {
                return $this->redirect(['/cabinet/default/login', 'org' => $this->_organizationId]);
            }
            return $this->redirect(['/cabinet/default/select-organization']);
        }

        return parent::beforeAction($action);
    }

    /**
     * Получить учеников текущего родителя
     * @return Pupil[]
     */
    protected function getPupils()
    {
        return $this->_pupils;
    }

    /**
     * Получить конкретного ученика по ID (с проверкой доступа)
     * @param int $id
     * @return Pupil|null
     * @throws ForbiddenHttpException
     */
    protected function getPupilById($id)
    {
        $pupilIds = Module::getPupilIds();

        if (!in_array($id, $pupilIds)) {
            throw new ForbiddenHttpException(Yii::t('app', 'У вас нет доступа к этому ученику'));
        }

        return Pupil::findOne(['id' => $id, 'is_deleted' => 0]);
    }

    /**
     * Получить ID организации
     * @return int
     */
    protected function getOrganizationId()
    {
        return $this->_organizationId;
    }

    /**
     * Получить ID групп для указанных учеников
     * @param array $pupilIds ID учеников
     * @return array ID групп
     */
    protected function getPupilGroupIds(array $pupilIds): array
    {
        if (empty($pupilIds)) {
            return [];
        }

        return EducationGroup::findWithDeleted()
            ->alias('eg')
            ->innerJoin('pupil_education pe', 'pe.id = eg.education_id')
            ->where(['pe.pupil_id' => $pupilIds])
            ->andWhere(['pe.is_deleted' => 0])
            ->andWhere(['eg.is_deleted' => 0])
            ->select('eg.group_id')
            ->column();
    }
}
