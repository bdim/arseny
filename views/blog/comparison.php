<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\ListView;
use yii\bootstrap\ActiveForm;
use app\models\Taxonomy;
use app\components\StringUtils;
use app\models\User;

$this->title = 'Хрон';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="filter-form">
    <form method="get" action="">


        <label for="tag">Сортировать: </label>
        <select name="sort">
            <? $selected = Yii::$app->request->get('sort'); ?>
            <option value="DESC" <?= ($selected != "ASC") ? 'selected' : ''?>>по убыванию</option>
            <option value="ASC"  <?= ($selected == "ASC") ? 'selected' : ''?>>по возрастанию</option>
        </select>

        <span>&nbsp;&nbsp;&nbsp;&nbsp;</span>
        <input type="submit" value="фильтровать">
    </form>
</div>
<?
echo ListView::widget([
        'dataProvider' => $dataProvider,
        'itemView' => '_comparisonList',
    ]);

?>


<style>
    .comp_col {
        display: inline-block;
        width: 49.8%;
        vertical-align: top;
    }
    .blog_item_title.m20{
        margin-bottom: 6px;
    }
</style>