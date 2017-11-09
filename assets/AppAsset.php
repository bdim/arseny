<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        'player/css/mkhplayer.default.css',
    ];
    public $js = [
        'player/js/jquery.mkhplayer.js',
        'js/app.js',
        'js/controllers.js',
        'js/directives.js',
        'js/services.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'app\assets\AngularAsset'
    ];

    public $jsOptions = ['position' => \yii\web\View::POS_HEAD];
}
