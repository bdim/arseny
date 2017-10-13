<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

$this->title = 'Calls list';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-info">
    <div class="user-name"><?=$user->fio; ?></div>
    <div class="user-info"><?=$user->info; ?></div>
</div>

<?
echo '<br><br>';
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],

        'callstart'     => [ 'attribute' => 'callstart', 'label' => 'Call'],
        'destination'   => [ 'attribute' => 'destination', 'label' => 'Phone'],
        'seconds'       => [ 'attribute' => 'duration', 'label' => 'Duration'],
        'RecordLink'    => [ 'content' => function($data){
            if ($data->is_recorded)
                return
                    '<audio id="record-'.$data->id.'" preload="metadata">'.
                    '<source src="'.$data->recordLink.'">';
            },
            'label' => 'Audio record', 'headerOptions' => ['width' => '300'], 'format' => 'raw'],
    ],
]);
?>
<script>
    $(function(){
        $('audio').mkhPlayer();
    })
</script>
