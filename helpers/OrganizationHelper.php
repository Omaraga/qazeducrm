<?php

namespace app\helpers;

use app\models\Organizations;
use Yii;

class OrganizationHelper
{

    public static function getUnblockedOrganizationId(array $organizations)
    {
        if (!Yii::$app->user->can("SUPER")) {
            if ($organizations) {
                foreach ($organizations as $organization) {
                    if (!Organizations::getList()[$organization->target_id]->is_disabled) {
                        return $organization->target_id;
                    }
                }
            }
        }
        return null;
    }

    public static function ignoredActions(): array
    {
        return [
            'disabled',
            'logout',
            'select',
            'exit-imitation'
        ];
    }

}