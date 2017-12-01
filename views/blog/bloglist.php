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

<style>
    .m20{
        margin: 20px;
    }
    .pt20 {
        padding-top: 20px;
    }
    .blog_item_media {
        min-height: 20px;
    }
    .filter-form{
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }
    .ug-textpanel-bg {
        opacity: 0 !important;
    }
    .blog_item {
        margin-top: 20px;
        background-color: #daecf5;
        box-shadow: 0 0 10px #666;
    }
    .event_body_item{
        background-color: #fdf4f7;
    }
    .blog_item_title {
        font-weight: bold;
    }
    .blog_item_one_title{
        color: #DD1144;
        display: block;
    }
    .blog_item_one_taxonomy {
        color: #006DA9;
    }
    .editable-input textarea{
        min-width: 500px;
    }
    .blog_item_one_body a{
        cursor: pointer !important;
        color : black !important;
    }
</style>

