<?php

namespace app\components;

class ActiveQuery extends \yii\db\ActiveQuery
{
    public $alias = null;


    /**
     * @param string $alias
     * @return $this
     */
    public function notDeleted($alias = null)
    {
        if (in_array('is_deleted',(new $this->modelClass())->attributes())) {
            return $this->onCondition(
                ($alias ?: $this->alias) . ' is_deleted != ' . ActiveRecord::DELETED
            );
        }
        return $this;
    }
}