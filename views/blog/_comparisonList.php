<?php
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use app\models\User;
use app\models\Blog;
use app\models\Files;
use app\models\Event;
use app\components\DateUtils;


    // записи из блога - только своим
    foreach ($model as $m)
    foreach ($m as $tag => $date) {
        if (!is_array($files[$tag]))
            $files[$tag] = [];
        $files[$tag] = array_merge($files[$tag],  Files::getItemsForDay($date['pub_date'], true));
        $blog [$tag][] = Blog::getItemsForDay($date['pub_date']);
        // $event[$tag][] = Event::getItemsForDay($date['pub_date']);
    }

    $tags = [];
    $out = [];
    if (!empty($blog)){
        foreach ($blog as $tag =>$m)
            foreach ($m as $items)
                foreach ($items as $item)
                    if (in_array($tag, $item->tagsIds) && (!empty($item->body) || !empty($item->title))){
                        $out['body'][$tag] .= $this->context->renderPartial('_body',['data' => $item]);
                    }
    }

    /* медиа */
    if (!empty($files)){
        foreach ($files as $tag => $items)
            $out['media'][$tag] = $this->context->renderPartial('_media',['data' => $items, 'show_date' => false]);

    }



    /* события показываем отдельно */
    /*if (!empty($event))
        foreach ($event as $tag => $m)
            foreach ($m as $items)
            foreach ($items as $item)  {
                $out['event'][$tag][$item->id]['title'] = Yii::$app->formatter->asDate($item['date_start'],'php:d.m.Y l') .
                    ( $item['date_start'] != $item['date_end'] ? " - ". Yii::$app->formatter->asDate($item['date_end'],'php:d.m.Y l') : '');

                $out['event'][$tag][$item->id]['body'] .= $this->context->renderPartial('_event',['data' => $item]);

                $files = Files::getItemsForEvent($item->id);
                if (!empty($files))
                    foreach ($files as $tag => $items)
                    {
                        $out['event'][$tag][$item->id]['media'] = $this->context->renderPartial('_media',['data' => $items, 'show_date' => true, 'event_id' => $item->id]);
                    }
            }*/


?>

<? if (!empty($out['body']) || !empty($out['media'])){ ?>
<div class="blog_item">
    <div class="blog_item_title m20 pt20"><?= $date['age'] ?>
        <?= !empty($age) ? '<span class="blog_item_age">('.implode(", ", $age).')</span>' : '';?></div>
    <div class="comp_col comp_left">
        <div class="blog_item_media"><?= $out['media'][2] ?></div>
        <div class="blog_item_body m20 "><?= $out['body'][2];?></div>
    </div><div
        class="comp_col comp_right">
        <div class="blog_item_media"><?= $out['media'][8] ?></div>
        <div class="blog_item_body m20 "><?= $out['body'][8];?></div>
    </div>


</div>
<hr>
<?}?>

<?/* if (!empty($out['event']))
    foreach ($out['event'] as $eventOut){*/?><!--
<div class="blog_item event_body_item">
    <div class="blog_item_title m20 pt20"><?/*= $eventOut['title'];*/?></div>
    <div class="blog_item_body m20 "><?/*= $eventOut['body'];*/?></div>
    <div class="blog_item_media"><?/*= $eventOut['media'] */?></div>
</div>
    <hr>
--><?/*}*/?>


