<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\ListView;


$this->title = 'Блог';
$this->params['breadcrumbs'][] = $this->title;

echo '<br><br>';
echo ListView::widget([
        'dataProvider' => $dataProvider,
        'itemView' => '_list',
    ]);

?>

<style>
    .blog_item div{
        margin-bottom: 15px;
    }
    .ug-textpanel-bg {
        opacity: 0 !important;
    }
    .blog_item {
        margin-top: 20px;
    }
    .blog_item_title {
        font-weight: bold;
    }
    .blog_item_one_taxonomy {
        color: #006DA9;
    }
</style>
