<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\bootstrap4\Html;

$this->title = Yii::t('main', 'Расписание');
$this->params['breadcrumbs'][] = $this->title;
$url = \app\helpers\OrganizationUrl::to(['schedule/events']);
$js = <<<JS
moment.locale('ru');
var now = moment();
var myCalendar = $('#calendar').Calendar({
    locale: 'ru',
    showNavigateButton: true,
    unixTimestamp: moment().format('X'),
    weekday:{
        timeline:{
            fromHour:8,
            toHour:22,
            intervalMinutes: 60
        }
    }
});
myCalendar.init();
function updateEvents(ev, cats){
    myCalendar.addEvents(ev);
    myCalendar.setEventCategoriesColors(cats)
    myCalendar.init();
}
function getEvents(){
    console.log(myCalendar);
     $.ajax({
        'url': '$url',
        'type': 'post',
        'data' : {start : myCalendar.fromTimestamp, end : myCalendar.toTimestamp},
        success: function(data){
            // data = JSON.parse(data);
            if (data){
                let events = [];
                let categories = [];
               for (let i = 0; i < data.length; i++){
                   let ev = {};
                   ev['start'] = data[i]['start'].toString();
                   ev['end'] = data[i]['end'].toString();
                   if (data[i]['status'] == 1){
                       ev['title'] = '<i class="fa fa-calendar-check-o" aria-hidden="true" style="font-size:13px;"></i> ' + data[i]['title'].toString();
                   }else{
                       ev['title'] = '<i class="fa fa-calendar-times-o" aria-hidden="true" style="font-size:13px;"></i> ' + data[i]['title'].toString();
                   }
                   ev['content'] = data[i]['content'].toString();
                   ev['category'] = data[i]['title'].toString();
                   ev['color'] = data[i]['color'];
                   ev['url'] = data[i]['url'];
                   let catExist = false;
                   categories.forEach(function (element){
                       if (element.category === ev['category']){
                           catExist = true;
                       }
                   })
                   if (!catExist){
                       categories.push({category : ev['category'], color: ev['color']});
                   }
                   events[i] = ev;
               }
               updateEvents(events, categories);
               
            }
        },
        error: function(data){
            alert('Error');
        }
    });
    
}


getEvents();
$('#calendar').on('Calendar.event-click',function(event, instance, elem, evt){
   $('#modal-form').find('#modalContent').load(evt.url);
    $('#modal-form').modal('show')
});
$('#calendar').on('Calendar.init', function(event, instance, before, current, after){
  
});
$('#main-container-block').removeClass('container').addClass('container-fluid');
JS;
$this->registerJs($js);
?>

<h3><?= Html::encode($this->title) ?></h3>
<p>
    <?= Html::button(Yii::t('main','Добавить занятие'),[
        'value' => \app\helpers\OrganizationUrl::to(['schedule/create']),
        'class' => 'btn btn-success modal-form',
        'id' => 'modalButton'
    ]) ?>
    <?=Html::a(Yii::t('main', 'Заполнить расписание'), \app\helpers\OrganizationUrl::to(['schedule/typical-schedule']), [
            'class' => 'btn btn-primary'
    ]);?>
</p>
<div class="alert alert-primary" role="alert">
    <p><i class="fa fa-calendar-check-o" aria-hidden="true" style="font-size:16px;"></i> - <?=Yii::t('main', 'Посещение проставлено');?></p>
    <p class="mb-0"><i class="fa fa-calendar-times-o" aria-hidden="true" style="font-size:16px;"></i> - <?=Yii::t('main', 'Посещение не проставлено, нужно заполнить');?></p>
</div>
<div id="calendar"></div>

<? \yii\bootstrap4\Modal::begin([
    'title' => Yii::t('main', 'Занятие'),
    'id' => 'modal-form',
    'size' => 'modal-lg'

]); ?>

<div id="modalContent"></div>
<? \yii\bootstrap4\Modal::end();?>

