<?php

namespace app\traits;

use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;

/**
 * Trait для унификации метода findModel() в контроллерах
 *
 * Использование в контроллере:
 *
 * ```php
 * class PupilController extends Controller
 * {
 *     use FindModelTrait;
 *
 *     // Указываем класс модели
 *     protected string $modelClass = Pupil::class;
 *
 *     // Опционально: сообщение об ошибке
 *     protected string $notFoundMessage = 'Ученик не найден';
 *
 *     // Опционально: использовать ли organization scope (по умолчанию true)
 *     protected bool $useOrganizationScope = true;
 * }
 * ```
 */
trait FindModelTrait
{
    /**
     * Найти модель по ID
     *
     * @param int|string $id
     * @return ActiveRecord
     * @throws NotFoundHttpException
     */
    protected function findModel($id): ActiveRecord
    {
        $modelClass = $this->getModelClass();
        $useOrgScope = $this->useOrganizationScope ?? true;

        /** @var \yii\db\ActiveQuery $query */
        $query = $modelClass::find()->andWhere(['id' => $id]);

        // Добавляем organization scope если нужно
        if ($useOrgScope && method_exists($query, 'byOrganization')) {
            $query->byOrganization();
        }

        // Добавляем notDeleted если модель использует soft delete
        if (method_exists($query, 'notDeleted')) {
            $query->notDeleted();
        }

        $model = $query->one();

        if ($model === null) {
            throw new NotFoundHttpException($this->getNotFoundMessage());
        }

        return $model;
    }

    /**
     * Найти модель по условию
     *
     * @param array $condition
     * @return ActiveRecord
     * @throws NotFoundHttpException
     */
    protected function findModelBy(array $condition): ActiveRecord
    {
        $modelClass = $this->getModelClass();
        $useOrgScope = $this->useOrganizationScope ?? true;

        /** @var \yii\db\ActiveQuery $query */
        $query = $modelClass::find()->andWhere($condition);

        if ($useOrgScope && method_exists($query, 'byOrganization')) {
            $query->byOrganization();
        }

        if (method_exists($query, 'notDeleted')) {
            $query->notDeleted();
        }

        $model = $query->one();

        if ($model === null) {
            throw new NotFoundHttpException($this->getNotFoundMessage());
        }

        return $model;
    }

    /**
     * Найти модель без organization scope (для superadmin)
     *
     * @param int|string $id
     * @return ActiveRecord
     * @throws NotFoundHttpException
     */
    protected function findModelGlobal($id): ActiveRecord
    {
        $modelClass = $this->getModelClass();

        $model = $modelClass::findOne($id);

        if ($model === null) {
            throw new NotFoundHttpException($this->getNotFoundMessage());
        }

        return $model;
    }

    /**
     * Найти модель включая удалённые (для восстановления)
     *
     * @param int|string $id
     * @return ActiveRecord
     * @throws NotFoundHttpException
     */
    protected function findModelWithDeleted($id): ActiveRecord
    {
        $modelClass = $this->getModelClass();
        $useOrgScope = $this->useOrganizationScope ?? true;

        // Используем findWithDeleted если доступно
        if (method_exists($modelClass, 'findWithDeleted')) {
            $query = $modelClass::findWithDeleted()->andWhere(['id' => $id]);
        } else {
            $query = $modelClass::find()->andWhere(['id' => $id]);
        }

        if ($useOrgScope && method_exists($query, 'byOrganization')) {
            $query->byOrganization();
        }

        $model = $query->one();

        if ($model === null) {
            throw new NotFoundHttpException($this->getNotFoundMessage());
        }

        return $model;
    }

    /**
     * Получить класс модели
     *
     * @return string
     */
    protected function getModelClass(): string
    {
        if (!isset($this->modelClass)) {
            throw new \LogicException(
                'Property $modelClass must be defined in ' . static::class
            );
        }
        return $this->modelClass;
    }

    /**
     * Получить сообщение об ошибке
     *
     * @return string
     */
    protected function getNotFoundMessage(): string
    {
        return $this->notFoundMessage ?? 'Запись не найдена';
    }
}
