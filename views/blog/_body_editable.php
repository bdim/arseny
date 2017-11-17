<?php
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\bootstrap\ActiveForm;
use dosamigos\editable\Editable;
use app\models\Taxonomy;
use app\components\StringUtils;

$form = ActiveForm::begin(['id' => 'form-body-'.$data->id, 'fieldConfig' => ['template' => "{input}"]]);

?>
<div><span class="blog_item_one_taxonomy"
        ><?

        $tags = Taxonomy::getVocabularyTags(Taxonomy::VID_BLOG_TAG);
        $source = [];
        foreach  ($tags as $tag)
            $source[] = [
                'value' => $tag->tid,
                'text'  => StringUtils::mb_ucfirst($tag->name)
            ];


        echo $form->field($data, 'tag')->widget(Editable::className(), [
            'url' => 'blog/update',
            'type' => 'checklist',
            'value' =>  implode(", ",$data->tagNames),
            //'mode' => 'pop',
            'clientOptions' => [
                'label' => 'Теги',
                'emptytext' => 'Про кого?',
                'value' =>  \yii\helpers\Json::encode($data->tag),
                'source' =>  $source
                    /*['value' => Taxonomy::TAG_ARSENY, 'text' => Taxonomy::$tag_case[Taxonomy::TAG_ARSENY]['и']],
                    ['value' => Taxonomy::TAG_YAROSLAV, 'text' => Taxonomy::$tag_case[Taxonomy::TAG_YAROSLAV]['и']],*/
                ,
            ]
        ]);

        ?></span><span class="blog_item_one_title"><?

        echo $form->field($data, 'title')->widget(Editable::className(), [
            'url' => 'blog/update',
            'type' => 'text',
            'mode' => 'pop',
            'clientOptions' => [
                'emptytext' => 'Заголовок',
                'placeholder' => 'Заголовок ...'
            ]
        ]);

?></span></div>

<div class="blog_item_one_body">
    <?
    echo $form->field($data, 'body')->widget(Editable::className(), [
        'url' => 'blog/update',
        'type' => 'wysihtml5',
        'clientOptions' => [
            'emptytext' => 'Текст',
        ]
    ]);
    ?>

</div>

<?  ActiveForm::end();?>