<?php

namespace app\widgets\tailwind;

use app\models\services\LidService;
use app\helpers\OrganizationUrl;
use Yii;
use yii\base\Widget;

/**
 * Виджет личной статистики менеджера по лидам
 */
class ManagerStatsWidget extends Widget
{
    /**
     * @var int|null ID менеджера. Если null - текущий пользователь
     */
    public $managerId = null;

    /**
     * @var bool Показывать компактную версию
     */
    public $compact = false;

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $managerId = $this->managerId ?? Yii::$app->user->id;

        if (!$managerId) {
            return '';
        }

        $stats = LidService::getManagerPersonalStats($managerId);
        $attentionLeads = LidService::getAttentionLeadsForManager($managerId, 5);

        return $this->render('manager-stats', [
            'stats' => $stats,
            'attentionLeads' => $attentionLeads,
            'compact' => $this->compact,
        ]);
    }
}
