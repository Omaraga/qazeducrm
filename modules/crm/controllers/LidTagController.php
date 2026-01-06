<?php

namespace app\modules\crm\controllers;

use app\helpers\OrganizationRoles;
use app\helpers\SystemRoles;
use app\models\LidTag;
use app\models\LidTagRelation;
use app\models\Organizations;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * LidTagController - управление тегами лидов
 */
class LidTagController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                        'create' => ['POST'],
                        'update' => ['POST'],
                        'toggle' => ['POST'],
                    ],
                ],
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => [
                                SystemRoles::SUPER,
                                OrganizationRoles::ADMIN,
                                OrganizationRoles::DIRECTOR,
                                OrganizationRoles::GENERAL_DIRECTOR,
                            ]
                        ],
                        [
                            'allow' => false,
                            'roles' => ['?']
                        ]
                    ],
                ],
            ]
        );
    }

    /**
     * AJAX: Получить список тегов организации
     */
    public function actionList()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $tags = LidTag::getOrganizationTags();

        // Если тегов нет - создаём дефолтные
        if (empty($tags)) {
            LidTag::createDefaults(Organizations::getCurrentOrganizationId());
            $tags = LidTag::getOrganizationTags();
        }

        $result = [];
        foreach ($tags as $tag) {
            $result[] = $tag->toArray();
        }

        return [
            'success' => true,
            'tags' => $result,
        ];
    }

    /**
     * AJAX: Создать новый тег
     */
    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $name = trim(Yii::$app->request->post('name', ''));
        $color = Yii::$app->request->post('color', 'gray');
        $icon = Yii::$app->request->post('icon', 'tag');

        if (empty($name)) {
            return ['success' => false, 'message' => 'Название обязательно'];
        }

        // Проверяем уникальность
        $exists = LidTag::find()
            ->byOrganization()
            ->andWhere(['name' => $name])
            ->notDeleted()
            ->exists();

        if ($exists) {
            return ['success' => false, 'message' => 'Тег с таким названием уже существует'];
        }

        $tag = new LidTag();
        $tag->organization_id = Organizations::getCurrentOrganizationId();
        $tag->name = $name;
        $tag->color = $color;
        $tag->icon = $icon;
        $tag->is_system = false;

        if ($tag->save()) {
            return [
                'success' => true,
                'message' => 'Тег создан',
                'tag' => $tag->toArray(),
            ];
        }

        return [
            'success' => false,
            'message' => 'Ошибка создания',
            'errors' => $tag->errors,
        ];
    }

    /**
     * AJAX: Обновить тег
     */
    public function actionUpdate($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $tag = $this->findModel($id);

        $name = trim(Yii::$app->request->post('name', ''));
        $color = Yii::$app->request->post('color');
        $icon = Yii::$app->request->post('icon');

        if (!empty($name)) {
            $tag->name = $name;
        }
        if ($color) {
            $tag->color = $color;
        }
        if ($icon) {
            $tag->icon = $icon;
        }

        if ($tag->save()) {
            return [
                'success' => true,
                'message' => 'Тег обновлён',
                'tag' => $tag->toArray(),
            ];
        }

        return [
            'success' => false,
            'message' => 'Ошибка обновления',
            'errors' => $tag->errors,
        ];
    }

    /**
     * AJAX: Удалить тег
     */
    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $tag = $this->findModel($id);

        // Нельзя удалить системный тег
        if ($tag->is_system) {
            return ['success' => false, 'message' => 'Системные теги нельзя удалять'];
        }

        // Удаляем связи
        LidTagRelation::deleteAll(['tag_id' => $id]);

        // Удаляем тег
        $tag->delete();

        return [
            'success' => true,
            'message' => 'Тег удалён',
        ];
    }

    /**
     * AJAX: Переключить тег на лиде
     */
    public function actionToggle()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $lidId = (int)Yii::$app->request->post('lid_id');
        $tagId = (int)Yii::$app->request->post('tag_id');

        if (!$lidId || !$tagId) {
            return ['success' => false, 'message' => 'Не указан лид или тег'];
        }

        // Проверяем существование связи
        $relation = LidTagRelation::findOne(['lid_id' => $lidId, 'tag_id' => $tagId]);

        if ($relation) {
            // Удаляем связь
            $relation->delete();
            return [
                'success' => true,
                'message' => 'Тег удалён',
                'hasTag' => false,
            ];
        } else {
            // Создаём связь
            $relation = new LidTagRelation();
            $relation->lid_id = $lidId;
            $relation->tag_id = $tagId;

            if ($relation->save()) {
                return [
                    'success' => true,
                    'message' => 'Тег добавлен',
                    'hasTag' => true,
                ];
            }
        }

        return ['success' => false, 'message' => 'Ошибка'];
    }

    /**
     * AJAX: Получить теги лида
     */
    public function actionGetLidTags($lid_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $relations = LidTagRelation::find()
            ->with('tag')
            ->where(['lid_id' => $lid_id])
            ->all();

        $tags = [];
        foreach ($relations as $rel) {
            if ($rel->tag) {
                $tags[] = $rel->tag->toArray();
            }
        }

        return [
            'success' => true,
            'tags' => $tags,
        ];
    }

    /**
     * Поиск модели тега
     */
    protected function findModel($id)
    {
        $model = LidTag::find()
            ->byOrganization()
            ->andWhere(['id' => $id])
            ->notDeleted()
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('Тег не найден');
        }

        return $model;
    }
}
