<?
use app\models\Files;
?>

<? if ($title) {?><h2><?= $title ?></h2><?}?>

<?
$photos = [];
$audios = [];
$items = [];
if (!empty($data) && (!empty($pub_date) || !empty($event_id)) && \app\models\User::isUserEditor()){?>
    <a class="blog_media_edit" title="подписать эти фото" href="/files?<?= $event_id ? "event_id=".$event_id : "date_id=".$pub_date; ?>" target="_blank"></a>
<?}
foreach($data as $file){
    if ($file->type_id == Files::TYPE_PHOTO){

        $photos[] =
            [
                'title' => ($show_date ? Yii::$app->formatter->asDate($file->date_id)." " : '').($file->caption ? $file->caption : ''),
                'href' => UPLOAD_WWW.'/'.$file->path,
                'type' => 'text/html',
                /*'poster' => 'http://media.w3.org/2010/05/sintel/poster.png'*/
            ];
    }


    if ($file->type_id == Files::TYPE_AUDIO){
        $audios[] = [
            'src'   => UPLOAD_WWW.'/'.$file->path,
            'type' => $file->param['mime-type']
        ];
    }

}

if (!empty($photos)){

    $gid = md5(uniqid().microtime(). rand(0, time()));
    echo dosamigos\gallery\Carousel::widget([
        'items' => $photos,
        'json' => true,
        'templateOptions' => ['id'=>'gallery_'.$gid],
        'clientOptions' => [
            'container'=>'#gallery_'.$gid,
            'startSlideshow' => false,
            'continuous' =>  false,
            'indicatorOptions' => [
                'thumbnailProperty' => 'thumbnail',
                'thumbnailIndicators' => true
            ],
        ],
        'options' => [
            'id'=>'gallery_'.$gid,

        ],
    ]);
}

if (!empty($audios)){
    foreach ($audios as $audio)
        echo \wbraganca\videojs\VideoJsWidget::widget([
            'options' => [
                'class' => 'video-js vjs-default-skin vjs-big-play-centered',
                //'poster' => "/upload/aposter.jpg",
                'controls' => true,
                'preload' => 'auto',
                /*'width' => '970',*/
                'style' => 'height: 30px; ',
            ],
            'tags' => [
                'source' => [$audio]
            ]
        ]);
}
?>