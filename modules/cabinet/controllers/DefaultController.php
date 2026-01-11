<?php

namespace app\modules\cabinet\controllers;

use app\models\Lesson;
use app\models\LessonAttendance;
use app\models\Organizations;
use app\models\Payment;
use app\models\Pupil;
use app\models\TelegramUser;
use app\modules\cabinet\models\LoginForm;
use app\modules\cabinet\Module;
use app\services\verification\TelegramVerificationService;
use Yii;
use yii\web\Response;

/**
 * DefaultController - главная страница и авторизация в личном кабинете
 */
class DefaultController extends CabinetBaseController
{
    /**
     * Dashboard - главная страница личного кабинета
     */
    public function actionIndex()
    {
        $pupils = $this->getPupils() ?? [];
        $organizationId = $this->getOrganizationId();

        // Получаем организацию для отображения названия
        $organization = Organizations::findOne($organizationId);

        // Собираем статистику для каждого ученика
        $pupilsData = [];
        foreach ($pupils as $pupil) {
            // Ближайшие занятия (на этой неделе)
            $today = date('Y-m-d');
            $weekEnd = date('Y-m-d', strtotime('+7 days'));

            $upcomingLessons = Lesson::findWithDeleted()
                ->alias('l')
                ->innerJoin('`group` g', 'g.id = l.group_id')
                ->innerJoin('education_group eg', 'eg.group_id = g.id')
                ->innerJoin('pupil_education pe', 'pe.id = eg.education_id')
                ->where(['pe.pupil_id' => $pupil->id])
                ->andWhere(['>=', 'l.date', $today])
                ->andWhere(['<=', 'l.date', $weekEnd])
                ->andWhere(['l.is_deleted' => 0])
                ->andWhere(['!=', 'l.status', Lesson::STATUS_CANCELED])
                ->orderBy(['l.date' => SORT_ASC, 'l.start_time' => SORT_ASC])
                ->limit(5)
                ->all();

            // Последние посещения
            $recentAttendances = LessonAttendance::findWithDeleted()
                ->alias('la')
                ->innerJoin('lesson l', 'l.id = la.lesson_id')
                ->where(['la.pupil_id' => $pupil->id])
                ->andWhere(['la.is_deleted' => 0])
                ->orderBy(['l.date' => SORT_DESC])
                ->limit(10)
                ->all();

            // Статистика посещаемости за месяц
            $monthStart = date('Y-m-01');
            $attendanceStats = LessonAttendance::findWithDeleted()
                ->alias('la')
                ->innerJoin('lesson l', 'l.id = la.lesson_id')
                ->where(['la.pupil_id' => $pupil->id])
                ->andWhere(['la.is_deleted' => 0])
                ->andWhere(['>=', 'l.date', $monthStart])
                ->select([
                    'total' => 'COUNT(*)',
                    'visited' => 'SUM(CASE WHEN la.status = ' . LessonAttendance::STATUS_VISIT . ' THEN 1 ELSE 0 END)',
                ])
                ->asArray()
                ->one();

            // Последние платежи
            $recentPayments = Payment::find()
                ->where(['pupil_id' => $pupil->id])
                ->andWhere(['is_deleted' => 0])
                ->orderBy(['date' => SORT_DESC])
                ->limit(5)
                ->all();

            $pupilsData[] = [
                'pupil' => $pupil,
                'upcomingLessons' => $upcomingLessons,
                'recentAttendances' => $recentAttendances,
                'attendanceStats' => $attendanceStats,
                'recentPayments' => $recentPayments,
            ];
        }

        return $this->render('index', [
            'pupilsData' => $pupilsData,
            'organization' => $organization,
        ]);
    }

    /**
     * Страница авторизации - выбор организации
     */
    public function actionSelectOrganization()
    {
        // Получаем список организаций
        $organizations = Organizations::find()
            ->where(['is_deleted' => 0])
            ->andWhere(['status' => Organizations::STATUS_ACTIVE])
            ->orderBy(['name' => SORT_ASC])
            ->all();

        return $this->render('select-organization', [
            'organizations' => $organizations,
        ]);
    }

    /**
     * Страница авторизации - ввод телефона
     */
    public function actionLogin($org = null)
    {
        // Если организация не выбрана - перенаправляем на выбор
        if (!$org) {
            return $this->redirect(['/cabinet/default/select-organization']);
        }

        // Проверяем что организация существует
        $organization = Organizations::findOne([
            'id' => $org,
            'is_deleted' => 0,
            'status' => Organizations::STATUS_ACTIVE,
        ]);

        if (!$organization) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Организация не найдена'));
            return $this->redirect(['/cabinet/default/select-organization']);
        }

        // Проверяем что кабинет включен для этой организации
        if ($organization->cabinet_enabled === false) {
            return $this->render('disabled', [
                'organization' => $organization,
            ]);
        }

        // Устанавливаем организацию в модуль для layout
        Module::$currentOrganization = $organization;

        // Если уже авторизован в этой организации - редирект на dashboard
        if (Module::checkParentAuth() && Module::getOrganizationId() == $org) {
            return $this->redirect(['/cabinet/default/index', 'org' => $org]);
        }

        $model = new LoginForm();
        $model->scenario = LoginForm::SCENARIO_PHONE;
        $model->organization_id = $org;

        if ($model->load(Yii::$app->request->post())) {
            $result = $model->sendCode();

            if ($result === true) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Код отправлен в Telegram'));
                return $this->redirect(['/cabinet/default/verify', 'org' => $org]);
            } elseif ($result === 'not_linked') {
                // Телефон не привязан к Telegram - показываем страницу привязки
                return $this->render('link-telegram', [
                    'organization' => $organization,
                    'phone' => $model->phone,
                ]);
            }
        }

        return $this->render('login', [
            'model' => $model,
            'organization' => $organization,
        ]);
    }

    /**
     * Подтверждение кода из Telegram
     */
    public function actionVerify($org = null)
    {
        // Проверяем что есть данные для верификации
        $session = Yii::$app->session;
        if (!$session->has('cabinet_temp_phone')) {
            return $this->redirect(['/cabinet/default/select-organization']);
        }

        // Получаем организацию из сессии
        $orgId = $org ?: $session->get('cabinet_temp_organization_id');
        if ($orgId) {
            $organization = Organizations::findOne(['id' => $orgId, 'is_deleted' => 0]);
            if ($organization) {
                Module::$currentOrganization = $organization;
            }
        }

        $model = new LoginForm();
        $model->scenario = LoginForm::SCENARIO_CODE;

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Вы успешно авторизовались'));
            $loggedOrgId = Module::getOrganizationId();
            return $this->redirect(['/cabinet/default/index', 'org' => $loggedOrgId]);
        }

        return $this->render('verify', [
            'model' => $model,
            'org' => $orgId,
        ]);
    }

    /**
     * Выход из личного кабинета
     */
    public function actionLogout($org = null)
    {
        $orgId = $org ?: Module::getOrganizationId();
        Module::logout();
        Yii::$app->session->setFlash('success', Yii::t('app', 'Вы вышли из личного кабинета'));

        if ($orgId) {
            return $this->redirect(['/cabinet/default/login', 'org' => $orgId]);
        }
        return $this->redirect(['/cabinet/default/select-organization']);
    }

    /**
     * Повторная отправка кода через Telegram
     */
    public function actionResendCode($org = null)
    {
        $session = Yii::$app->session;

        $phone = $session->get('cabinet_temp_phone');
        $orgId = $org ?: $session->get('cabinet_temp_organization_id');

        if (!$phone || !$orgId) {
            return $this->redirect(['/cabinet/default/select-organization']);
        }

        // Устанавливаем организацию для layout
        $organization = Organizations::findOne(['id' => $orgId, 'is_deleted' => 0]);
        if ($organization) {
            Module::$currentOrganization = $organization;
        }

        // Отправляем код через Telegram
        $service = new TelegramVerificationService();
        $result = $service->sendCode($phone, $orgId);

        if ($result['success']) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Код отправлен повторно'));
        } else {
            Yii::$app->session->setFlash('error', $result['message'] ?? Yii::t('app', 'Не удалось отправить код'));
        }

        return $this->redirect(['/cabinet/default/verify', 'org' => $orgId]);
    }

    /**
     * Проверка привязки телефона к Telegram (AJAX)
     */
    public function actionCheckLinked($org)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $phone = Yii::$app->session->get('cabinet_pending_phone');

        if (!$phone) {
            return ['linked' => false, 'error' => 'no_phone'];
        }

        $service = new TelegramVerificationService();
        $linked = $service->isPhoneLinked($phone);

        return ['linked' => $linked];
    }
}
