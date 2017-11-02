<?php
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use app\models\Blog;
use app\models\Files;

    $blog = Blog::getItemsForDay($model['pub_date']);
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
    <div class="blog_item_title"><?= Yii::$app->formatter->asDate($model['pub_date']) ?> <? //= Html::encode($model->title) ?></div>
    <div class="blog_item_taxonomy"><? //= implode(", ",$model->tagNames) ?></div>
    <div class="blog_item_body"><?= HtmlPurifier::process($out['body'])?></div>
    <div class="blog_item_photo"><?= $out['photo'] ?></div>
</div>
<hr>