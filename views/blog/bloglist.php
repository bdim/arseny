<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;


$this->title = 'Блог';
$this->params['breadcrumbs'][] = $this->title;

echo '<br><br>';
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        //['class' => 'yii\grid\SerialColumn'],

        //'id',
        'publish_date' => [ 'label' => 'Опубликовано', 'content' => function($data){ return Yii::$app->formatter->asDate($data->publish_date);}],
        'tag' => [ 'label' => 'Ключевые слова', 'content' => function($data){ return implode(", ",$data->tagNames);}],
        'title' => [ 'attribute' => 'title', 'label' => 'Заголовок'],
        'body'  => [ 'attribute' => 'body', 'label' => '', 'content' => function($data){ return $data->body;}],
        //['class' => 'yii\grid\ActionColumn','template' => '{update} {delete}'],
    ],
]);
?>