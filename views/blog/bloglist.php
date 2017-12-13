<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\ListView;
use yii\bootstrap\ActiveForm;
use app\models\Taxonomy;
use app\components\StringUtils;
use app\models\User;

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

        <span>&nbsp;&nbsp;&nbsp;&nbsp;</span>

        <label for="tag">Сортировать: </label>
        <select name="sort">
            <? $selected = Yii::$app->request->get('sort'); ?>
            <option value="DESC" <?= ($selected != "ASC") ? 'selected' : ''?>>по убыванию</option>
            <option value="ASC"  <?= ($selected == "ASC") ? 'selected' : ''?>>по возрастанию</option>
        </select>

        <? if (User::isUserAdmin()){
            $selected = intval(Yii::$app->request->get('notags'));
            ?>
            <span>&nbsp;&nbsp;&nbsp;&nbsp;</span>
            <label for="notags">без тегов: </label>
            <input name="notags" type="checkbox" value="1" <?= $selected ? 'checked' : ''?>>
        <?}?>
        <span>&nbsp;&nbsp;&nbsp;&nbsp;</span>
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
    @media screen and (max-device-width: 400px) {
        .editable-input textarea.input-small{
            min-width:  300px;
        }
    }
    @media screen and (max-device-width: 500px) and (min-device-width: 401px) {
        .editable-input textarea.input-small{
            min-width:  400px;
        }
    }
</style>


