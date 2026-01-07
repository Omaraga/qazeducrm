<?php

namespace app\components;

use Yii;
use yii\web\Controller;
use app\models\relations\UserOrganization;
use app\behaviors\SubscriptionWarningBehavior;

class BaseController extends Controller
{
    /**
     * @var bool Включить проверку подписки и показ предупреждений
     */
    public bool $enableSubscriptionWarnings = true;

    public $aliases = [
        'ru' => 'ru-RU',
        'kz' => 'kk-KZ',
        'en' => 'en-US',
    ];
    public $description = '';
    public $keywords = '';
    public $title = '';
    public $image = null;

    private $langParam = 'language';
    public function init()
    {
        $ln = \Yii::$app->request->get('ln');
        if($ln && key_exists($ln, $this->aliases)) {
            $this->setLanguage($this->aliases[$ln]);
        } else if (\Yii::$app->session->get($this->langParam)){
            \Yii::$app->language = \Yii::$app->session->get($this->langParam);
        }
        if (!Yii::$app->user->isGuest){
            $user = Yii::$app->user->identity;
            if (!$user->active_organization_id){
                $userOrg = UserOrganization::find()->where(['related_id' => $user->id])->notDeleted()->one();
                if ($userOrg){
                    Yii::$app->user->identity->active_organization_id = $userOrg->target_id;
                }
            }

        }
        parent::init();

    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Добавляем проверку подписки и предупреждения
        if ($this->enableSubscriptionWarnings) {
            $behaviors['subscriptionWarning'] = [
                'class' => SubscriptionWarningBehavior::class,
                'checkLimits' => true,
                'checkSubscription' => true,
                'limitWarningThreshold' => 80,
                'limitCriticalThreshold' => 95,
                'subscriptionWarningDays' => 7,
            ];
        }

        return $behaviors;
    }

    public function setLanguage($language)
    {
        \Yii::$app->language = $language;
        \Yii::$app->session->set($this->langParam, $language);
    }
    public function registerMetaTags(){
        \Yii::$app->view->registerMetaTag([
            'name' => 'description',
            'content' => $this->description,
        ]);
        \Yii::$app->view->registerMetaTag([
            'name' => 'keywords',
            'content' => $this->keywords,
        ]);
        Yii::$app->view->title = $this->title;
    }

    public function initMetaTags($title, $keywords, $description){
        $this->description = $description;
        $this->keywords = $keywords;
        $this->title = $title;
        $this->registerMetaTags();
    }



}