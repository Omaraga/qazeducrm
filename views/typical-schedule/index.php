<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\bootstrap4\Html;

$this->title = Yii::t('main', 'Типовое расписание');
$this->params['breadcrumbs'][] = $this->title;
$url = \app\helpers\OrganizationUrl::to(['typical-schedule/events']);
$js = <<<JS
moment.locale('ru');
var now = moment();
var myCalendar = $('#calendar').Calendar({
    locale: 'ru',
    showNavigateButton: false,
    unixTimestamp: moment('2024-01-01').format('X'),
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
     $.ajax({
        'url': '$url',
        'type': 'post',
        success: function(data){
            data = JSON.parse(data);
            if (data){
                let events = [];
                let categories = [];
               for (let i = 0; i < data.length; i++){
                   let ev = {};
                   ev['start'] = data[i]['start'].toString();
                   ev['end'] = data[i]['end'].toString();
                   ev['title'] = data[i]['title'].toString();
                   ev['content'] = data[i]['content'].toString();
                   ev['category'] = data[i]['category'].toString();
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
  console.log('ev', event);
  console.log('instance', instance);
  console.log('elem', elem);
  console.log('evt', evt);
   $('#modal-form').find('#modalContent').load(evt.url);
    $('#modal-form').modal('show')
});

JS;
$this->registerJs($js);
?>

<h3><?= Html::encode($this->title) ?></h3>
<p>
    <?= Html::button(Yii::t('main','Добавить занятие'),[
        'value' => \app\helpers\OrganizationUrl::to(['typical-schedule/create']),
        'class' => 'btn btn-success modal-form',
        'id' => 'modalButton'
    ]) ?>
</p>
<div id="calendar"></div>

<? \yii\bootstrap4\Modal::begin([
    'title' => Yii::t('main', 'Добавить занятие'),
    'id' => 'modal-form',
    'size' => 'modal-lg'

]); ?>

<div id="modalContent"></div>
<? \yii\bootstrap4\Modal::end();?>

