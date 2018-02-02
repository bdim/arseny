<?php
/* @var $this yii\web\View */
$this->title = 'Привет! Арсений.';
?>
<div class="breadcrumb">Все новости смотри в <a href="<?= Yii::$app->urlManager->createUrl("blog/index")?>">блоге</a></div>
<?= \app\widgets\MediaWidget::widget(['pub_date' => \app\models\Event::getLastDate()]); ?>
