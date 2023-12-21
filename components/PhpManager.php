<?php
namespace app\components;

use Yii;
use yii\rbac\Assignment;

class PhpManager extends \yii\rbac\PhpManager
{
    public function init()
    {
        parent::init();
    }

    public function getAssignments($userId)
    {

        if (Yii::$app->user->isGuest)
            return [];

        $assignment = new Assignment;
        $assignment->userId = $userId;

        $currentOrganizationRole = Yii::$app->user->identity->currentOrganizationRole ?? null;
        $assignment->roleName = $currentOrganizationRole ?: Yii::$app->user->identity->system_role;

        return [$assignment->roleName => $assignment];
    }
}