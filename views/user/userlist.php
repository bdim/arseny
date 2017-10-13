<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;


$this->title = 'User list';
$this->params['breadcrumbs'][] = $this->title;

echo Html::a('Add new user',Url::to(['user/add']));
echo '<br><br>';
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        //['class' => 'yii\grid\SerialColumn'],

        'id',
        'username' => [ 'attribute' => 'username', 'label' => 'Site Login'],
        'fio' => [ 'attribute' => 'fio', 'label' => 'User Name'],
        'info',
        ['class' => 'yii\grid\ActionColumn','template' => '{update} {delete}'],
    ],
]);
?>