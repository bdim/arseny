<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\ListView;
use yii\bootstrap\ActiveForm;
use app\models\Taxonomy;
use app\components\StringUtils;


$this->title = 'Блог';
$this->params['breadcrumbs'][] = $this->title;

$tags = Taxonomy::getVocabularyTags(Taxonomy::VID_BLOG_TAG);
?>
<div class="filter-form">
    <form method="get" action="">
        <label for="year">Год: </label>
        <select name="year">
            <option value="">-</option>
            <?
            $selected = intval(Yii::$app->request->get('year'));
            $years = array_reverse(range(2012, date("Y")));
            foreach ($years as $year){?>
                <option value="<?=$year?>" <?= ($selected == $year) ? 'selected' : ''?> ><?=$year?></option>
            <?}?>
        </select>

        <span>&nbsp;&nbsp;&nbsp;&nbsp;</span>

        <label for="tag">Про кого: </label>
        <select name="tag">
            <option value="">-</option>
            <?
            $selected = intval(Yii::$app->request->get('tag'));
            foreach  ($tags as $tag){?>
                <option value="<?=$tag->tid ?>" <?= ($selected == $tag->tid) ? 'selected' : ''?> ><?=StringUtils::mb_ucfirst($tag->name)?></option>
            <?}?>
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


