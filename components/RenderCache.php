<?php
namespace app\components;

use yii\caching\TagDependency;
use yii\web\Controller;

class RenderCache extends Controller
{

    public function init()
    {
        parent::init();
    }

    public static function cacheId($key){
        return \Yii::$app->getRequest()->serverName . $key;
    }

    public static function getCacheDependency($modelName){
        return new TagDependency(['tags' => \Yii::$app->getRequest()->serverName . $modelName]);
    }

    public static function flushCache($modelName){
        TagDependency::invalidate(\Yii::$app->cache, \Yii::$app->getRequest()->serverName . $modelName);
    }

}