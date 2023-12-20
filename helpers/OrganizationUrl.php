<?php
namespace app\helpers;

use app\models\Organizations;
use yii\helpers\Url;

class OrganizationUrl extends Url
{

    public static function to($url = '', $scheme = false)
    {

        $organization_id = Organizations::getCurrentOrganizationId();
        if (is_array($url) AND $organization_id) {
            $url['oid'] = $organization_id;
        }

        return parent::to($url, $scheme);
    }

}