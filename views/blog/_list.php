<?php
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use app\models\User;
use app\models\Blog;
use app\models\Files;

    // записи из блога - только своим
    if (!Yii::$app->user->isGuest) {
        $blog = Blog::getItemsForDay($model['pub_date']);
    }

    $files = Files::getItemsForDay($model['pub_date']);

    $out = [];
    if (!empty($blog)){
        foreach ($blog as $item) {
            if (User::isUserAdmin())
                $out['body'] .= $this->context->renderPartial('_body_editable',['data' => $item]);
            else
                $out['body'] .= $this->context->renderPartial('_body',['data' => $item]);
        }
    } elseif (User::isUserAdmin()){
        $item = new Blog();
        $item->publish_date = $model['pub_date'];
        $item->save();

        $out['body'] .= $this->context->renderPartial('_body_editable',['data' => $item]);
    }

    if (!empty($files)){
        $out['media'] = $this->context->renderPartial('_media',['data' => $files]);
    }


?>

<div class="blog_item">
    <div class="blog_item_title"><?= Yii::$app->formatter->asDate($model['pub_date'],'php:d.m.Y l') ?></div>
    <div class="blog_item_body"><?= $out['body'];?></div>
    <div class="blog_item_media"><?= $out['media'] ?></div>
</div>
<hr>

<style>
    .editable-input textarea{
        min-width: 500px;
    }
    .blog_item_one_body a{
        cursor: pointer !important;
        color : black !important;
    }
</style>