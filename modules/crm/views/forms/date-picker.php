<?php
/* @var $this \yii\web\View*/
/* @var $model \yii\base\Model*/
/* @var $attribute string */
/* @var $modelName string */
$js = <<<JS
$(function () {
  $('.datepicker').datepicker({
    language: "ru",
    autoclose: true,
    format: "dd.mm.yyyy"
  });
});
JS;
$this->registerJs($js);
?>

<!-- Date Picker -->
<div class="form-group mb-4 col-4">
    <div class="datepicker date input-group field-<?=strtolower($modelName);?>-<?=$attribute;?>">
        <input type="text" placeholder="" class="form-control"  name="<?=$modelName;?>[<?=$attribute;?>]" id="<?=strtolower($modelName);?>-<?=$attribute;?>">
        <div class="input-group-append">
            <span class="input-group-text"><i class="fa fa-calendar"></i></span>
        </div>
    </div>
</div>
<!-- // Date Picker -->
