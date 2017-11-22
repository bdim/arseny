<?
use app\models\Files;

    $photos = [];
    $audios = [];
    foreach($data as $file){
        if ($file->type_id == Files::TYPE_PHOTO)
            $photos[] = [
                'thumb' => Files::thumb(UPLOAD_PATH.'/'.$file->path, 150),
                'src'   => UPLOAD_WWW.'/'.$file->path,
                'description' => $file->caption ? $file->caption : null
            ];

        if ($file->type_id == Files::TYPE_AUDIO){
            $audios[] = [
                'src'   => UPLOAD_WWW.'/'.$file->path,
                'type' => $file->param['mime-type']
            ];
        }

    }

    if (!empty($photos)){
        echo  \diplodok\Gallerywidget\GalleryWidget::widget([
            //'title_gallery' => 'Заголовок', // опция
            'theme' => 'grid', // опция (по умолчанию тема grid) grid, tiles, tilesgrid, slider, default, compact, carousel
            'photos' => $photos
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