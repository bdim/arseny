<?php
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use app\models\Blog;
use app\models\Files;

    // записи из блога - только своим
    if (!Yii::$app->user->isGuest) {
        $blog = Blog::getItemsForDay($model['pub_date']);
    }

    $files = Files::getItemsForDay($model['pub_date']);

    $out = [];
    if (!empty($blog))
        foreach ($blog as $item) {
            $out['body'] .= $this->context->renderPartial('_body',['data' => $item]);
        }
    if (!empty($files))
        $out['photo'] = $this->context->renderPartial('_photo',['data' => $files]);

?>

<div class="blog_item">
    <div class="blog_item_title"><?= Yii::$app->formatter->asDate($model['pub_date'],'php:d.m.Y l') ?></div>
    <div class="blog_item_body"><?= HtmlPurifier::process($out['body'])?></div>
    <div class="blog_item_photo"><?= $out['photo'] ?></div>
</div>
<hr>