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
                    'type' => 'datetime',
                    'mode' => 'pop',
                    'clientOptions' => [
                        'placement' => 'right',
                        'format' => 'yyyy-mm-dd',
                        'viewformat' => 'dd/mm/yyyy hh:ii',
                        'datetimepicker' => [
                            'orientation' => 'top auto'
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
                    'type' => 'datetime',
                    'mode' => 'pop',
                    'clientOptions' => [
                        'placement' => 'right',
                        'format' => 'yyyy-mm-dd',
                        'viewformat' => 'dd/mm/yyyy hh:ii',
                        'datetimepicker' => [
                            'orientation' => 'top auto'
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
                        'placement' => 'right',
                        'format' => 'yyyy-mm-dd hh:ii:ss',
                        'viewformat' => 'dd/mm/yyyy hh:ii',
                        'datetimepicker' => [
                            'orientation' => 'top auto'
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
                        'emptytext' => 'Текст',
                    ]
                ]);
            },
        ],

        ['class' => 'yii\grid\ActionColumn','template' => ' {delete}'],
    ],
]);
?>
