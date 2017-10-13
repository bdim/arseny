<?php
namespace app\components;

use yii\web\Controller;

class InitController extends Controller
{
    public static $lang = 'en';

    public function init()
    {
        parent::init();

        \Yii::$app->language = 'en-EN';
        //\Yii::$app->lang = 'ru';
    }
}