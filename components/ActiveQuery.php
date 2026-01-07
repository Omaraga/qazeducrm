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

        return $this->andWhere([
            "$this->alias.organization_id" => $organization_id ?: $currentOrgId
        ]);
    }

    /**
     * @param string $alias
     * @return $this
     */
    public function notDeleted($alias = null)
    {
        if (in_array('is_deleted',(new $this->modelClass())->attributes())) {
            return $this->onCondition(
                ($alias ? $alias.'.': $this->alias) . 'is_deleted != ' . ActiveRecord::DELETED
            );
        }
        return $this;
    }
}