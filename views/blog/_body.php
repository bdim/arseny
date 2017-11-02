<?php
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;?>

    <div><? if (!empty($data->tagNames)){?><span class="blog_item_one_taxonomy"
            ><?= implode(", ",$data->tagNames) ?></span> <?}?><span class="blog_item_one_title"><?= Html::encode($data->title) ?></span></div>
    <div class="blog_item_one_body"><?= HtmlPurifier::process($data->body)?></div>

