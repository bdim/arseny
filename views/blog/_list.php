<?php
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
?>

<div class="blog_item">
    <div class="blog_item_title"><?= Yii::$app->formatter->asDate($model->publish_date) ?> <?= Html::encode($model->title) ?></div>
    <div class="blog_item_taxonomy"><?= implode(", ",$model->tagNames) ?></div>
    <div class="blog_item_body"><?= HtmlPurifier::process($model->body)?></div>
</div>
<hr>