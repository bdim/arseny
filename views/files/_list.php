<?
use app\models\Files;
use app\models\Taxonomy;
use dosamigos\editable\Editable;
use app\components\StringUtils;
use yii\widgets\ActiveForm;

$photos = [];
$audios = [];
$items = [];

$file = $model;
$tags = Taxonomy::getVocabularyTags(Taxonomy::VID_BLOG_TAG);

if ($file->type_id == Files::TYPE_PHOTO){

    $photos[] =
        [
            'title' => Yii::$app->formatter->asDate($file->date_id)." ".($file->caption ? $file->caption : ''),
            'href' => UPLOAD_WWW.'/'.$file->path,
            'type' => 'text/html',

        ];
}


if ($file->type_id == Files::TYPE_AUDIO){
    $audios[] = [
        'src'   => UPLOAD_WWW.'/'.$file->path,
        'type' => $file->param['mime-type']
    ];
}



?>

    <div class="blog_item">
        <div class="blog_item_title m20 pt20"><?
            $form = ActiveForm::begin(['id' => 'form-body-'.$model->id, 'fieldConfig' => ['template' => "{input}"]]);

            ?>
            <div><span class="blog_item_one_taxonomy"
                    ><?

                    $source = [];
                    foreach  ($tags as $tag)
                        $source[] = [
                            'value' => $tag->tid,
                            'text'  => StringUtils::mb_ucfirst($tag->name)
                        ];
                    $source[] = [
                        'value' => 0,
                        'text'  => '-пусто-'
                    ];

                    echo $form->field($model, 'tag')->widget(Editable::className(), [
                        'url' => 'files/update',
                        'type' => 'checklist',
                        'value' =>  implode(", ",$model->tagNames),
                        //'mode' => 'pop',
                        'clientOptions' => [
                            'label' => 'Теги',
                            'emptytext' => 'Про кого?',
                            'value' =>  \yii\helpers\Json::encode($model->tag),
                            'source' =>  $source
                            /*['value' => Taxonomy::TAG_ARSENY, 'text' => Taxonomy::$tag_case[Taxonomy::TAG_ARSENY]['и']],
                            ['value' => Taxonomy::TAG_YAROSLAV, 'text' => Taxonomy::$tag_case[Taxonomy::TAG_YAROSLAV]['и']],*/
                            ,
                        ]
                    ]);

                    ?></span><span class="blog_item_one_title"><?

                    echo $form->field($model, 'caption')->widget(Editable::className(), [
                        'url' => 'files/update',
                        'type' => 'text',
                        'mode' => 'pop',
                        'clientOptions' => [
                            'emptytext' => 'Заголовок',
                            'placeholder' => 'Заголовок ...'
                        ]
                    ]);

                    ?></span></div>



            <?  ActiveForm::end();?>

        </div>
        <div class="blog_item_media">


<?
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
        </div>
    </div>
<hr>
