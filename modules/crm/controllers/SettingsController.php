<?php

namespace app\modules\crm\controllers;

use app\helpers\OrganizationRoles;
use app\helpers\OrganizationUrl;
use app\helpers\RoleChecker;
use app\helpers\SystemRoles;
use app\models\OrganizationAccessSettings;
use app\models\Organizations;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * SettingsController - настройки организации
 */
class SettingsController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'access' => ['GET', 'POST'],
                        'organization' => ['GET', 'POST'],
                        'ajax-save-organization' => ['POST'],
                        'upload-logo' => ['POST'],
                        'delete-logo' => ['POST'],
                    ],
                ],
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        // Доступ к настройкам - только для директоров
                        [
                            'allow' => true,
                            'roles' => ['@'],
                            'matchCallback' => function ($rule, $action) {
                                return RoleChecker::isDirector();
                            }
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Настройки прав доступа
     * @return string|\yii\web\Response
     */
    public function actionAccess()
    {
        $model = OrganizationAccessSettings::getForOrganization();
        $settings = $model->getSettingsArray();

        if ($this->request->isPost) {
            $postSettings = $this->request->post('settings', []);

            // Преобразуем checkbox значения в boolean
            $newSettings = [];
            foreach (OrganizationAccessSettings::DEFAULTS as $key => $default) {
                $newSettings[$key] = isset($postSettings[$key]) && $postSettings[$key];
            }

            $model->setSettingsArray($newSettings);

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Настройки доступа сохранены');
                return $this->redirect(['access']);
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка сохранения настроек');
            }

            $settings = $newSettings;
        }

        return $this->render('access', [
            'model' => $model,
            'settings' => $settings,
            'groups' => OrganizationAccessSettings::GROUPS,
            'labels' => OrganizationAccessSettings::LABELS,
            'hints' => OrganizationAccessSettings::HINTS,
        ]);
    }

    /**
     * AJAX сохранение одной настройки
     * @return array
     */
    public function actionAjaxSaveSetting()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!$this->request->isAjax || !$this->request->isPost) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        $key = $this->request->post('key');
        $value = $this->request->post('value');

        if (!$key || !array_key_exists($key, OrganizationAccessSettings::DEFAULTS)) {
            return ['success' => false, 'message' => 'Invalid setting key'];
        }

        $model = OrganizationAccessSettings::getForOrganization();
        $model->setSetting($key, (bool)$value);

        if ($model->save()) {
            return [
                'success' => true,
                'message' => 'Сохранено',
                'key' => $key,
                'value' => (bool)$value
            ];
        }

        return ['success' => false, 'message' => 'Ошибка сохранения'];
    }

    /**
     * Главная страница настроек (редирект на организацию)
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        return $this->redirect(['organization']);
    }

    /**
     * Настройки организации
     * @return string
     */
    public function actionOrganization()
    {
        $organization = Organizations::getCurrentOrganization();

        return $this->render('organization', [
            'organization' => $organization,
        ]);
    }

    /**
     * AJAX сохранение поля настроек организации
     * @return array
     */
    public function actionAjaxSaveOrganization()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isAjax || !$this->request->isPost) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        $field = $this->request->post('field');
        $value = $this->request->post('value');

        // Список разрешённых полей
        $dbFields = ['name', 'legal_name', 'bin', 'phone', 'email', 'address', 'timezone', 'locale'];
        $infoFields = [
            'instagram', 'whatsapp', 'telegram',
            'currency', 'date_format',
            'work_hours_start', 'work_hours_end', 'working_days', 'first_day_of_week',
            'default_lesson_duration', 'auto_deduct_enabled', 'lesson_notifications_enabled',
        ];

        $allowedFields = array_merge($dbFields, $infoFields);

        if (!in_array($field, $allowedFields)) {
            return ['success' => false, 'message' => 'Invalid field'];
        }

        $organization = Organizations::getCurrentOrganization();

        // Декодируем JSON если это массив или boolean
        if (is_string($value) && in_array($field, ['working_days', 'auto_deduct_enabled', 'lesson_notifications_enabled'])) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }

        // Устанавливаем значение
        $organization->$field = $value;

        // Для DB полей используем частичное сохранение
        if (in_array($field, $dbFields)) {
            if ($organization->save(true, [$field])) {
                return ['success' => true, 'message' => 'Сохранено'];
            }
        } else {
            // Для info полей сохраняем весь объект
            if ($organization->save(false)) {
                return ['success' => true, 'message' => 'Сохранено'];
            }
        }

        return ['success' => false, 'message' => 'Ошибка сохранения', 'errors' => $organization->errors];
    }

    /**
     * Загрузка логотипа организации
     * @return array
     */
    public function actionUploadLogo()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $file = UploadedFile::getInstanceByName('logo');
        if (!$file) {
            return ['success' => false, 'message' => 'Файл не загружен'];
        }

        // Валидация расширения
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array(strtolower($file->extension), $allowedExtensions)) {
            return ['success' => false, 'message' => 'Недопустимый формат файла. Разрешены: ' . implode(', ', $allowedExtensions)];
        }

        // Валидация размера (2MB)
        if ($file->size > 2 * 1024 * 1024) {
            return ['success' => false, 'message' => 'Файл слишком большой. Максимум 2MB'];
        }

        $organization = Organizations::getCurrentOrganization();

        // Создаём директорию
        $uploadDir = Yii::getAlias('@webroot/uploads/organizations/' . $organization->id);
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Удаляем старый логотип
        if ($organization->logo) {
            $oldPath = Yii::getAlias('@webroot') . $organization->logo;
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        // Сохраняем новый
        $fileName = 'logo_' . time() . '.' . $file->extension;
        $filePath = $uploadDir . '/' . $fileName;

        if ($file->saveAs($filePath)) {
            $organization->logo = '/uploads/organizations/' . $organization->id . '/' . $fileName;
            $organization->save(false);

            return [
                'success' => true,
                'message' => 'Логотип загружен',
                'url' => $organization->logo,
            ];
        }

        return ['success' => false, 'message' => 'Ошибка сохранения файла'];
    }

    /**
     * Удаление логотипа организации
     * @return array
     */
    public function actionDeleteLogo()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $organization = Organizations::getCurrentOrganization();

        if ($organization->logo) {
            $oldPath = Yii::getAlias('@webroot') . $organization->logo;
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        $organization->logo = null;
        $organization->save(false);

        return ['success' => true, 'message' => 'Логотип удалён'];
    }
}
