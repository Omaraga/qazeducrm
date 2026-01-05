<?php

namespace app\modules\crm\controllers;

use app\helpers\OrganizationRoles;
use app\helpers\SystemRoles;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * CrmBaseController - базовый контроллер для CRM модуля
 *
 * Предоставляет:
 * - Стандартные behaviors (AccessControl, VerbFilter)
 * - Общие методы для JSON ответов
 * - Хелперы для flash-сообщений
 */
abstract class CrmBaseController extends Controller
{
    /**
     * Стандартные роли для доступа к CRM
     */
    protected array $allowedRoles = [
        SystemRoles::SUPER,
        OrganizationRoles::ADMIN,
        OrganizationRoles::DIRECTOR,
        OrganizationRoles::GENERAL_DIRECTOR,
    ];

    /**
     * Дополнительные действия требующие POST запрос
     */
    protected array $postActions = ['delete'];

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
                    'actions' => $this->getVerbActions(),
                ],
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => $this->getAccessRules(),
                ],
            ]
        );
    }

    /**
     * Получить правила для VerbFilter
     */
    protected function getVerbActions(): array
    {
        $actions = [];
        foreach ($this->postActions as $action) {
            $actions[$action] = ['POST'];
        }
        return $actions;
    }

    /**
     * Получить правила доступа
     */
    protected function getAccessRules(): array
    {
        return [
            [
                'allow' => true,
                'roles' => $this->allowedRoles,
            ],
            [
                'allow' => false,
                'roles' => ['?'],
            ],
        ];
    }


    /**
     * Вернуть успешный JSON ответ
     */
    protected function jsonSuccess($data = null, string $message = null): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $response = ['success' => true];
        if ($message !== null) {
            $response['message'] = $message;
        }
        if ($data !== null) {
            $response['data'] = $data;
        }
        return $response;
    }

    /**
     * Вернуть ошибочный JSON ответ
     */
    protected function jsonError(string $message, $errors = null): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $response = [
            'success' => false,
            'message' => $message,
        ];
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        return $response;
    }

    /**
     * Установить flash сообщение об успехе
     */
    protected function setFlashSuccess(string $message): void
    {
        Yii::$app->session->setFlash('success', $message);
    }

    /**
     * Установить flash сообщение об ошибке
     */
    protected function setFlashError(string $message): void
    {
        Yii::$app->session->setFlash('error', $message);
    }

    /**
     * Установить flash предупреждение
     */
    protected function setFlashWarning(string $message): void
    {
        Yii::$app->session->setFlash('warning', $message);
    }

    /**
     * Проверить, является ли запрос AJAX POST
     */
    protected function isAjaxPost(): bool
    {
        return Yii::$app->request->isAjax && Yii::$app->request->isPost;
    }

    /**
     * Проверить, является ли запрос AJAX
     */
    protected function isAjax(): bool
    {
        return Yii::$app->request->isAjax;
    }
}
