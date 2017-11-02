<?
use app\models\Files;

    $photos = [];
    foreach($data as $image){
        $photos[] = [
            'thumb' => Files::thumb(IMAGES_PATH.'/'.$image->path, 150),
            'src'   => '/upload/'.$image->path,
            'description' => $image->caption ? $image->caption : null
        ];
    }

?>
<?= \diplodok\Gallerywidget\GalleryWidget::widget([
    //'title_gallery' => 'Заголовок', // опция
    'theme' => 'grid', // опция (по умолчанию тема grid) grid, tiles, tilesgrid, slider, default, compact, carousel
    'photos' => $photos
]); ?>