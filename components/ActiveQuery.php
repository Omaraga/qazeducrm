<?php

namespace app\components;

use app\models\Organizations;

class ActiveQuery extends \yii\db\ActiveQuery
{
    public $alias = null;

    /**
     * @param null $organization_id
     * @return $this|ActiveQuery
     */
    public function byOrganization($organization_id = null)
    {
        $currentOrgId = Organizations::getCurrentOrganizationId();

        // Если организация не указана и текущая org_id = 0 или null (SUPER или CLI) - не фильтруем
        if ($organization_id === null && ($currentOrgId === 0 || $currentOrgId === null)) {
            // AND \Yii::$app->user->can("SUPER")
            return $this;
        }

        // Определяем колонку с учётом alias (если alias не задан, используем просто имя колонки)
        $column = $this->alias ? "{$this->alias}.organization_id" : 'organization_id';

        return $this->andWhere([
            $column => $organization_id ?: $currentOrgId
        ]);
    }

    /**
     * @param string $alias
     * @return $this
     */
    public function notDeleted($alias = null)
    {
        if (in_array('is_deleted', (new $this->modelClass())->attributes())) {
            // Определяем префикс таблицы (alias или пустая строка)
            $prefix = $alias ? "{$alias}." : ($this->alias ? "{$this->alias}." : '');
            return $this->onCondition(
                $prefix . 'is_deleted != ' . ActiveRecord::DELETED
            );
        }
        return $this;
    }
}