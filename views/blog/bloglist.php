<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\ListView;
use yii\bootstrap\ActiveForm;


$this->title = 'Блог';
$this->params['breadcrumbs'][] = $this->title;
 ?>

<div class="filter-form">
    <form method="get" action="">
        <label for="year">Год</label>
        <select name="year">
            <option value="">-</option>

        <?  $selected = intval(Yii::$app->request->get('year'));
            $years = array_reverse(range(2012, date("Y")));
            foreach ($years as $year){?>
                <option value="<?=$year?>" <?= ($selected == $year) ? 'selected' : ''?> ><?=$year?></option>
        <?}?>

    </select>
    <input type="submit" value="фильтровать">
    </form>
</div>
<?
echo '<br>';
echo ListView::widget([
        'dataProvider' => $dataProvider,
        'itemView' => '_list',
    ]);

?>

<style>
    .blog_item div{
        margin-bottom: 15px;
    }
    .ug-textpanel-bg {
        opacity: 0 !important;
    }
    .blog_item {
        margin-top: 20px;
    }
    .blog_item_title {
        font-weight: bold;
    }
    .blog_item_one_taxonomy {
        color: #006DA9;
    }
</style>
