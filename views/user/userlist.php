<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use dosamigos\editable\Editable;
use app\models\User;

$this->title = 'User list';
$this->params['breadcrumbs'][] = $this->title;

echo Html::a('Add new user',Url::to(['user/add']));
echo '<br><br>';
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        //['class' => 'yii\grid\SerialColumn'],

        'id',
        'username' => [
            'label' => 'Site Login',
            'format' => 'raw',
            'value' => function($data){
                return Editable::widget( [
                    'model' => $data,
                    'attribute' => 'username',
                    'url' => 'user/updateone',
                    'type' => 'text',

                    'clientOptions' => [
                        'showbuttons' => 'bottom',
                        'emptytext' => 'Site Login',
                        'placeholder' => 'Site Login ...'
                    ]
                ]);
            },
        ],
        'role' => [
            'label' => 'Role',
            'format' => 'raw',
            'value' => function($data){
                return Editable::widget([
                    'model' => $data,
                    'attribute' => 'role',
                    'url' => 'user/updateone',
                    'type' => 'select',

                    'clientOptions' => [
                        'value' => $data->role,
                        'source' => [
                            ['value' =>  User::ROLE_ADMIN,  'text' => 'admin'],
                            ['value' =>  User::ROLE_EDITOR, 'text' => 'editor'],
                            ['value' =>  User::ROLE_USER,   'text' => 'user'],
                        ]            ,
                        'showbuttons' => 'bottom',
                        'emptytext' => 'Role',
                        'placeholder' => 'Role ...'
                    ]
                ]);
            },
        ],
        'fio' =>[
            'label' => 'User Name',
            'format' => 'raw',
            'value' => function($data){
                return Editable::widget( [
                    'model' => $data,
                    'attribute' => 'fio',
                    'url' => 'user/updateone',
                    'type' => 'text',

                    'clientOptions' => [
                        'showbuttons' => 'bottom',
                        'emptytext' => 'User Name',
                        'placeholder' => 'User Name ...'
                    ]
                ]);
            },
        ],
        'info' => [
            'label' => 'Info',
            'format' => 'raw',
            'value' => function($data){
                return Editable::widget( [
                    'model' => $data,
                    'attribute' => 'info',
                    'url' => 'user/updateone',
                    'type' => 'text',

                    'clientOptions' => [
                        'showbuttons' => 'bottom',
                        'emptytext' => 'Info',
                        'placeholder' => 'Info ...'
                    ]
                ]);
            },
        ],
        ['class' => 'yii\grid\ActionColumn','template' => '{update} {delete}'],
    ],
]);
?>