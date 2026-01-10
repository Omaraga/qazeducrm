<?php

namespace app\modules\crm\controllers;

use app\helpers\OrganizationRoles;
use app\helpers\SystemRoles;
use app\models\Notification;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * NotificationController - управление уведомлениями
 */
class NotificationController extends CrmBaseController
{
    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'mark-read' => ['POST'],
                        'mark-all-read' => ['POST'],
                        'delete' => ['POST'],
                    ],
                ],
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => ['@'], // Любой авторизованный пользователь
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * AJAX: Получить уведомления для dropdown
     */
    public function actionGetNotifications()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $userId = Yii::$app->user->id;
        $notifications = Notification::getUnreadForUser($userId, 10);
        $unreadCount = Notification::getUnreadCountForUser($userId);

        $items = [];
        foreach ($notifications as $notif) {
            $items[] = [
                'id' => $notif->id,
                'type' => $notif->type,
                'type_icon' => $notif->getTypeIcon(),
                'type_class' => $notif->getTypeClass(),
                'title' => $notif->title,
                'message' => $notif->message,
                'link' => $notif->link,
                'is_read' => (bool)$notif->is_read,
                'time_ago' => $notif->getTimeAgo(),
                'created_at' => $notif->created_at,
            ];
        }

        return [
            'success' => true,
            'notifications' => $items,
            'unread_count' => $unreadCount,
        ];
    }

    /**
     * AJAX: Получить количество непрочитанных
     */
    public function actionGetUnreadCount()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $userId = Yii::$app->user->id;
        $count = Notification::getUnreadCountForUser($userId);

        return [
            'success' => true,
            'count' => $count,
        ];
    }

    /**
     * AJAX: Отметить уведомление как прочитанное
     */
    public function actionMarkRead()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $id = Yii::$app->request->post('id');
        $userId = Yii::$app->user->id;

        $notification = Notification::findOne(['id' => $id, 'user_id' => $userId]);

        if (!$notification) {
            return ['success' => false, 'message' => 'Уведомление не найдено'];
        }

        if ($notification->markAsRead()) {
            return [
                'success' => true,
                'unread_count' => Notification::getUnreadCountForUser($userId),
            ];
        }

        return ['success' => false, 'message' => 'Ошибка'];
    }

    /**
     * AJAX: Отметить все как прочитанные
     */
    public function actionMarkAllRead()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $userId = Yii::$app->user->id;
        $count = Notification::markAllAsReadForUser($userId);

        return [
            'success' => true,
            'marked_count' => $count,
            'unread_count' => 0,
        ];
    }

    /**
     * Страница всех уведомлений
     */
    public function actionIndex()
    {
        $userId = Yii::$app->user->id;
        $notifications = Notification::getAllForUser($userId, 100);

        return $this->render('index', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * AJAX: Создать напоминание для лида
     */
    public function actionCreateLidReminder()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $lidId = Yii::$app->request->post('lid_id');
        $scheduledAt = Yii::$app->request->post('scheduled_at');
        $message = Yii::$app->request->post('message');

        if (!$lidId || !$scheduledAt) {
            return ['success' => false, 'message' => 'Укажите лида и время'];
        }

        $lid = \app\models\Lids::find()
            ->byOrganization()
            ->andWhere(['id' => $lidId])
            ->notDeleted()
            ->one();

        if (!$lid) {
            return ['success' => false, 'message' => 'Лид не найден'];
        }

        $userId = Yii::$app->user->id;
        $notification = Notification::createLidReminder($lid, $userId, $scheduledAt, $message);

        if ($notification) {
            return [
                'success' => true,
                'message' => 'Напоминание создано',
                'notification_id' => $notification->id,
            ];
        }

        return ['success' => false, 'message' => 'Ошибка создания напоминания'];
    }
}
