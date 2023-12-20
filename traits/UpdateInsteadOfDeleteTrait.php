<?php
namespace app\traits;

use app\components\ActiveRecord;

/**
 * Используйте данный трейт во всех моделях где нужно чтобы запись не удалялась при delete()
 * Class UpdateInsteadOfDeleteTrait
 * @package app\components
 */
trait UpdateInsteadOfDeleteTrait
{

    /**
     * Методы удаления для записей, которые не нужно удалять, а ставить конолнку is_deleted = 1;
     */
    public function delete()
    {
        if (in_array('is_deleted',$this->attributes())) {
            $this->is_deleted = ActiveRecord::DELETED;
        }
        return $this->save(false);
    }

    public static function deleteAll($condition = '', $params = [], $force = false) {
        if ($force) return parent::deleteAll($condition, $params);
        $c = static::className();

        $dp = [];

        if ( in_array('is_deleted',(new $c)->attributes()) ) {
            $dp = [
                "is_deleted" => ActiveRecord::DELETED
            ];
        }
        return parent::updateAll($dp, $condition, $params);
    }


}

?>