<?php

namespace common\components;

use Yii;
use yii\web\View;

class BaseWidget extends \yii\bootstrap4\Widget
{

    // путь до папок assets
    protected $assets_path = '';

    protected $js = [];
    protected $css = [];

    public function init()
    {
        $this->publishAssets();
    }

    private function publishAssets()
    {
        $assets = Yii::$app->assetManager->publish($this->assets_path);
        if(isset($assets[1])) {

            if (!empty($this->css)) {
                foreach ($this->css as $css_path) {
                    $this->getView()->registerCssFile($assets[1] .'/'. rtrim($css_path, '/'));
                }
            }

            if (!empty($this->js)) {
                foreach ($this->js as $js_path) {
                    $this->getView()->registerJsFile($assets[1] .'/'. rtrim($js_path, '/'), [
                        'position' => View::POS_END
                    ]);
                }
            }

        }
    }

}
