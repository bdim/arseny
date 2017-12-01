<?php
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use app\models\User;
use app\models\Blog;
use app\models\Files;
use app\models\Event;

    // записи из блога - только своим
    if (!Yii::$app->user->isGuest) {
        $blog  = Blog::getItemsForDay($model['pub_date']);
        $event = Event::getItemsForDay($model['pub_date']);
    }

    $files = Files::getItemsForDay($model['pub_date'], true);

    $out = [];
    if (!empty($blog)){
        foreach ($blog as $item) {

            if (User::isUserAdmin())
                $out['body'] .= $this->context->renderPartial('_body_editable',['data' => $item, 'controller' => 'blog']);
            else
                $out['body'] .= $this->context->renderPartial('_body',['data' => $item]);
        }
    } elseif (!empty($files) && User::isUserAdmin()){
        $item = new Blog();
        $item->publish_date = $model['pub_date'];
        $item->save();

        $out['body'] .= $this->context->renderPartial('_body_editable',['data' => $item]);
    }

    /* медиа */
    if (!empty($files)){
        $out['media'] = $this->context->renderPartial('_media',['data' => $files, 'show_date' => false]);
    }


    /* события показываем отдельно */
    if (!empty($event))
        foreach ($event as $item) {

            $out['event'][$item->id]['title'] = Yii::$app->formatter->asDate($item['date_start'],'php:d.m.Y l') .
                ( $item['date_start'] != $item['date_end'] ? " - ". Yii::$app->formatter->asDate($item['date_end'],'php:d.m.Y l') : '');

            if (User::isUserAdmin())
                $out['event'][$item->id]['body'] .= $this->context->renderPartial('_event_editable',['data' => $item, 'controller' => 'event']);
            else
                $out['event'][$item->id]['body'] .= $this->context->renderPartial('_event',['data' => $item]);

            $files = Files::getItemsForEvent($item->id);
            if (!empty($files)){
                $out['event'][$item->id]['media'] = $this->context->renderPartial('_media',['data' => $files, 'show_date' => true]);
            }
        }
?>

<? if (!empty($out['body']) || !empty($out['media'])){ ?>
<div class="blog_item">
    <div class="blog_item_title m20 pt20"><?= Yii::$app->formatter->asDate($model['pub_date'],'php:d.m.Y l') ?></div>
    <div class="blog_item_body m20 "><?= $out['body'];?></div>
    <div class="blog_item_media"><?= $out['media'] ?></div>
</div>
<hr>
<?}?>

<? if (!empty($out['event']))
    foreach ($out['event'] as $eventOut){?>
<div class="blog_item event_body_item">
    <div class="blog_item_title m20 pt20"><?= $eventOut['title'];?></div>
    <div class="blog_item_body m20 "><?= $eventOut['body'];?></div>
    <div class="blog_item_media"><?= $eventOut['media'] ?></div>
</div>
    <hr>
<?}?>
