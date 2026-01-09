<?php

namespace app\controllers;

use app\models\forms\OrganizationRegistrationForm;
use app\models\Organizations;
use app\models\OrganizationActivityLog;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;

/**
 * RegistrationController - регистрация новых организаций.
 */
class RegistrationController extends Controller
{
    /**
     * Используем landing layout для страниц регистрации
     */
    public $layout = 'landing';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'success', 'verify-email', 'awaiting-approval'],
                        'allow' => true,
                        'roles' => ['?'], // Только для гостей
                    ],
                    [
                        'actions' => ['index', 'success', 'verify-email', 'awaiting-approval'],
                        'allow' => true,
                        'roles' => ['@'], // Также для авторизованных (для verify-email)
                    ],
                ],
            ],
        ];
    }

    /**
     * Форма регистрации организации
     *
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        // Если пользователь авторизован, перенаправляем в CRM
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/crm']);
        }

        $model = new OrganizationRegistrationForm();
        $plans = OrganizationRegistrationForm::getPlanOptions();

        if ($model->load(Yii::$app->request->post()) && $model->register()) {
            // Отправляем письмо с подтверждением
            $model->sendVerificationEmail();

            // Сохраняем данные в сессию для страницы успеха
            Yii::$app->session->set('registration_success', [
                'org_name' => $model->org_name,
                'org_email' => $model->org_email,
                'admin_email' => $model->admin_email,
            ]);

            return $this->redirect(['success']);
        }

        return $this->render('index', [
            'model' => $model,
            'plans' => $plans,
        ]);
    }

    /**
     * Страница успешной регистрации
     *
     * @return string|\yii\web\Response
     */
    public function actionSuccess()
    {
        $data = Yii::$app->session->get('registration_success');

        if (!$data) {
            return $this->redirect(['index']);
        }

        // Очищаем данные из сессии после показа
        Yii::$app->session->remove('registration_success');

        return $this->render('success', [
            'orgName' => $data['org_name'],
            'orgEmail' => $data['org_email'],
            'adminEmail' => $data['admin_email'],
        ]);
    }

    /**
     * Подтверждение email организации
     *
     * @param string $token Токен верификации
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionVerifyEmail($token)
    {
        $organization = Organizations::find()
            ->andWhere(['verification_token' => $token])
            ->andWhere(['is_deleted' => 0])
            ->one();

        if (!$organization) {
            throw new NotFoundHttpException('Неверная или устаревшая ссылка для подтверждения.');
        }

        // Проверяем, не подтверждён ли уже email
        if ($organization->email_verified_at !== null) {
            Yii::$app->session->setFlash('info', 'Email уже был подтверждён ранее.');

            // Если организация уже активна - показываем страницу верификации
            if ($organization->status === Organizations::STATUS_ACTIVE) {
                return $this->render('verify-email', [
                    'success' => true,
                    'alreadyVerified' => true,
                    'organization' => $organization,
                ]);
            }

            // Если ещё на модерации - показываем страницу ожидания
            return $this->render('awaiting-approval', [
                'organization' => $organization,
            ]);
        }

        // Подтверждаем email, НО НЕ активируем организацию!
        // Организация остаётся в статусе PENDING до одобрения супер-админом
        $organization->email_verified_at = date('Y-m-d H:i:s');
        $organization->verification_token = null; // Очищаем токен
        // НЕ меняем статус: $organization->status остаётся PENDING

        if ($organization->save()) {
            // Логируем активность
            OrganizationActivityLog::log(
                $organization->id,
                OrganizationActivityLog::ACTION_EMAIL_VERIFIED,
                OrganizationActivityLog::CATEGORY_ORGANIZATION,
                'Email организации подтверждён. Ожидает одобрения администратором.'
            );

            // Показываем страницу ожидания одобрения
            return $this->render('awaiting-approval', [
                'organization' => $organization,
            ]);
        }

        Yii::$app->session->setFlash('error', 'Не удалось подтвердить email. Попробуйте позже.');

        return $this->render('verify-email', [
            'success' => false,
            'organization' => $organization,
        ]);
    }

    /**
     * Страница ожидания одобрения (для повторного доступа)
     *
     * @return string|\yii\web\Response
     */
    public function actionAwaitingApproval()
    {
        // Получаем email из сессии или параметра
        $email = Yii::$app->request->get('email');

        if (!$email) {
            return $this->redirect(['index']);
        }

        $organization = Organizations::find()
            ->andWhere(['email' => $email])
            ->andWhere(['is_deleted' => 0])
            ->one();

        if (!$organization) {
            return $this->redirect(['index']);
        }

        // Если организация уже активна - редирект на логин
        if ($organization->status === Organizations::STATUS_ACTIVE) {
            Yii::$app->session->setFlash('success', 'Ваша организация одобрена! Теперь вы можете войти.');
            return $this->redirect(['/site/login']);
        }

        return $this->render('awaiting-approval', [
            'organization' => $organization,
        ]);
    }
}
