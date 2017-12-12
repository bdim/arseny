<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\ListView;
use yii\bootstrap\ActiveForm;
use app\models\Taxonomy;
use app\components\StringUtils;
use app\models\User;

$this->title = 'Медиа файлы';
$this->params['breadcrumbs'][] = $this->title;

$events = \app\models\Event::find()->orderBy('date_start ASC')->all();
?>
<div class="filter-form">
    <form method="get" action="">
        <label for="date_id">Дата: </label>
        <input name="date_id" value="<?= $filter['date_id'] ?>">

        <span>&nbsp;&nbsp;&nbsp;&nbsp;</span>

        <label for="event_id">Событие: </label>
        <select name="event_id">
            <option value="">-</option>
            <?
            $selected = intval(Yii::$app->request->get('event_id'));
            foreach  ($events as $event){?>
                <option value="<?=$event->id ?>" <?= ($selected == $event->id) ? 'selected' : ''?> ><?=$event->date_start.' '.$event->title?></option>
            <?}?>
        </select>

        <span>&nbsp;&nbsp;&nbsp;&nbsp;</span>

        <label for="tag">Сортировать: </label>
        <select name="sort">
            <? $selected = Yii::$app->request->get('sort'); ?>
            <option value="ASC"  <?= ($selected != "DESC") ? 'selected' : ''?>>по возрастанию</option>
            <option value="DESC" <?= ($selected == "DESC") ? 'selected' : ''?>>по убыванию</option>
        </select>

        <input type="submit" value="фильтровать">
    </form>
</div>
<?
echo ListView::widget([
        'dataProvider' => $dataProvider,
        'itemView' => '_list',
    ]);

?>


