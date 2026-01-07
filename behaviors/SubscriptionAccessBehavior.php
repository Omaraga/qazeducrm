<?php

namespace app\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\ActionEvent;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use app\services\SubscriptionAccessService;

/**
 * Behavior для автоматической проверки доступа на основе подписки
 *
 * Использование в контроллере:
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'subscriptionAccess' => [
 *             'class' => SubscriptionAccessBehavior::class,
 *             'createActions' => ['create', 'import'],
 *             'updateActions' => ['update'],
 *             'deleteActions' => ['delete'],
 *             'viewActions' => ['view', 'index'],
 *         ],
 *     ];
 * }
 * ```
 */
class SubscriptionAccessBehavior extends Behavior
{
    /**
     * @var array Действия, требующие права на создание
     */
    public array $createActions = ['create'];

    /**
     * @var array Действия, требующие права на редактирование
     */
    public array $updateActions = ['update'];

    /**
     * @var array Действия, требующие права на удаление
     */
    public array $deleteActions = ['delete'];

    /**
     * @var array Действия, требующие права на просмотр
     */
    public array $viewActions = [];

    /**
     * @var array Действия, исключённые из проверки
     */
    public array $except = [];

    /**
     * @var bool Показывать flash-сообщение при блокировке
     */
    public bool $showFlashMessage = true;

    /**
     * @var string|null URL для редиректа при блокировке (null = ForbiddenHttpException)
     */
    public ?string $redirectUrl = null;

    /**
     * @var bool Включить проверку (можно отключить для определённых условий)
     */
    public bool $enabled = true;

    /**
     * {@inheritdoc}
     */
    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction',
        ];
    }

    /**
     * Проверка доступа перед выполнением действия
     */
    public function beforeAction(ActionEvent $event): bool
    {
        if (!$this->enabled) {
            return true;
        }

        // Пропускаем консольные команды
        if (Yii::$app instanceof \yii\console\Application) {
            return true;
        }

        // Пропускаем гостей (они проверяются RBAC)
        if (Yii::$app->user->isGuest) {
            return true;
        }

        $actionId = $event->action->id;

        // Проверяем исключения
        if (in_array($actionId, $this->except)) {
            return true;
        }

        $accessService = SubscriptionAccessService::forCurrentOrganization();

        // Проверка действий создания
        if (in_array($actionId, $this->createActions)) {
            if (!$accessService->canCreate()) {
                return $this->handleAccessDenied($event, 'create', $accessService);
            }
        }

        // Проверка действий редактирования
        if (in_array($actionId, $this->updateActions)) {
            if (!$accessService->canUpdate()) {
                return $this->handleAccessDenied($event, 'update', $accessService);
            }
        }

        // Проверка действий удаления
        if (in_array($actionId, $this->deleteActions)) {
            if (!$accessService->canDelete()) {
                return $this->handleAccessDenied($event, 'delete', $accessService);
            }
        }

        // Проверка действий просмотра
        if (!empty($this->viewActions) && in_array($actionId, $this->viewActions)) {
            if (!$accessService->canView()) {
                return $this->handleAccessDenied($event, 'view', $accessService);
            }
        }

        // Блокировка всех действий если режим BLOCKED
        $mode = $accessService->getAccessMode();
        if ($mode === SubscriptionAccessService::MODE_BLOCKED) {
            // Разрешаем только страницу подписки и выход
            $allowedActions = ['subscription', 'logout', 'renew', 'payment'];
            $controllerId = $event->action->controller->id;

            if (!in_array($controllerId, $allowedActions) && !in_array($actionId, ['logout'])) {
                return $this->handleAccessDenied($event, 'blocked', $accessService);
            }
        }

        return true;
    }

    /**
     * Обработка отказа в доступе
     */
    protected function handleAccessDenied(
        ActionEvent $event,
        string $operationType,
        SubscriptionAccessService $accessService
    ): bool {
        $mode = $accessService->getAccessMode();
        $message = $this->getAccessDeniedMessage($operationType, $mode);

        if ($this->showFlashMessage) {
            Yii::$app->session->setFlash('error', $message);
        }

        if ($this->redirectUrl !== null) {
            $event->isValid = false;
            Yii::$app->response->redirect($this->redirectUrl);
            return false;
        }

        // Для AJAX-запросов возвращаем JSON
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->statusCode = 403;
            Yii::$app->response->data = [
                'success' => false,
                'message' => $message,
                'accessMode' => $mode,
                'redirectUrl' => '/subscription/renew',
            ];
            $event->isValid = false;
            return false;
        }

        throw new ForbiddenHttpException($message);
    }

    /**
     * Получить сообщение об отказе в доступе
     */
    protected function getAccessDeniedMessage(string $operationType, string $mode): string
    {
        $messages = [
            'create' => [
                SubscriptionAccessService::MODE_LIMITED => 'Создание новых записей ограничено. Подписка истекла.',
                SubscriptionAccessService::MODE_READ_ONLY => 'Режим только для чтения. Продлите подписку для создания записей.',
                SubscriptionAccessService::MODE_BLOCKED => 'Доступ заблокирован. Необходимо оплатить подписку.',
            ],
            'update' => [
                SubscriptionAccessService::MODE_READ_ONLY => 'Режим только для чтения. Редактирование недоступно.',
                SubscriptionAccessService::MODE_BLOCKED => 'Доступ заблокирован. Необходимо оплатить подписку.',
            ],
            'delete' => [
                SubscriptionAccessService::MODE_LIMITED => 'Удаление записей ограничено. Подписка истекла.',
                SubscriptionAccessService::MODE_READ_ONLY => 'Режим только для чтения. Удаление недоступно.',
                SubscriptionAccessService::MODE_BLOCKED => 'Доступ заблокирован. Необходимо оплатить подписку.',
            ],
            'view' => [
                SubscriptionAccessService::MODE_BLOCKED => 'Доступ заблокирован. Необходимо оплатить подписку.',
            ],
            'blocked' => [
                SubscriptionAccessService::MODE_BLOCKED => 'Доступ к системе заблокирован. Для продолжения работы необходимо оплатить подписку.',
            ],
        ];

        return $messages[$operationType][$mode]
            ?? 'Действие недоступно в текущем режиме подписки.';
    }
}
