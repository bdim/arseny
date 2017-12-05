<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use dosamigos\editable\Editable;

$this->title = 'События';
$this->params['breadcrumbs'][] = $this->title;

echo Html::a('Добавить',Url::to(['event/add']));
echo '<br><br>';
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'options' => [
        'class' => 'event-list',
    ],
    'columns' => [
        //['class' => 'yii\grid\SerialColumn'],
        'id',
        [
            'label' => 'Дата начала',
            'format' => 'raw',
            'value' => function($data){
                return Editable::widget( [
                    'model' => $data,
                    'attribute' => 'date_start',
                    'url' => 'event/update',
                    'type' => 'date',
                    /*'mode' => 'pop',*/
                    'clientOptions' => [
                        'showbuttons' => 'bottom',
                        'format' => 'yyyy-mm-dd',
                        'viewformat' => 'yyyy-mm-dd',
                        'datepicker' => [
                            'weekStart'=> 1
                        ]
                    ]
                ]);
            },
        ],[
            'label' => 'Дата конца',
            'format' => 'raw',
            'value' => function($data){
                return Editable::widget( [
                    'model' => $data,
                    'attribute' => 'date_end',
                    'url' => 'event/update',
                    'type' => 'date',
                    /*'mode' => 'pop',*/
                    'clientOptions' => [
                        'showbuttons' => 'bottom',
                        'format' => 'yyyy-mm-dd',
                        'viewformat' => 'yyyy-mm-dd',
                        'datepicker' => [
                            'weekStart'=> 1
                        ]
                    ]
                ]);
            },
        ],[
            'label' => 'Дата публикации',
            'format' => 'raw',
            'value' => function($data){
                return Editable::widget( [
                    'model' => $data,
                    'attribute' => 'publish_date',
                    'url' => 'event/update',
                    'type' => 'datetime',

                    'clientOptions' => [
                        'showbuttons' => 'bottom',
                        'format' => 'yyyy-mm-dd hh:ii:ss',
                        'viewformat' => 'yyyy-mm-dd hh:ii',
                        'datetimepicker' => [
                            'weekStart'=> 1,
                        ]
                    ]
                ]);
            },
        ],[
            'label' => 'Заголовок',
            'format' => 'raw',
            'value' => function($data){
                return Editable::widget( [
                    'model' => $data,
                    'attribute' => 'title',
                    'url' => 'event/update',
                    'type' => 'text',

                    'clientOptions' => [
                        'showbuttons' => 'bottom',
                        'emptytext' => 'Заголовок',
                        'placeholder' => 'Заголовок ...'
                    ]
                ]);
            },
        ],[
            'label' => 'Текст',
            'format' => 'raw',
            'value' => function($data){
                return Editable::widget( [
                    'model' => $data,
                    'attribute' => 'body',
                    'url' => 'event/update',
                    'type' => 'wysihtml5',
                    'clientOptions' => [
                        'showbuttons' => 'bottom',
                        'emptytext' => 'Текст',
                    ]
                ]);
            },
        ],

        ['class' => 'yii\grid\ActionColumn','template' => ' {delete}'],
    ],
]);
?>
<style>
    .event-list {
        position: relative;
    }
</style>