<?php
namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

class AngularAsset extends AssetBundle
{
    public $sourcePath = '@bower';
    public $js = [
        'angular/angular.js',
        'angular-animate/angular-animate.min.js',
        'angular-bootstrap/ui-bootstrap.min.js',
        'angular-bootstrap/ui-bootstrap-tpls.min.js',

        'js/app.js',
        'js/controllers.js',
        'js/directives.js',
        'js/services.js',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
}