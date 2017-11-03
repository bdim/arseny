<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\ListView;


$this->title = 'Статьи';
$this->params['breadcrumbs'][] = $this->title;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        //['class' => 'yii\grid\SerialColumn'],

        //'id',
        //'publish_date' => [ 'label' => 'Опубликовано', 'content' => function($data){ return Yii::$app->formatter->asDate($data->publish_date);}],
        'title' => [ 'attribute' => 'title', 'label' => 'Заголовок'],
        'body'  => [ 'attribute' => 'body', 'label' => '', 'content' => function($data){ return $data->body;}],
        //['class' => 'yii\grid\ActionColumn','template' => '{update} {delete}'],
    ],
]);
