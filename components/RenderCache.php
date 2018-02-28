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
        $serverName = isset(\Yii::$app->getRequest()->serverName) ? \Yii::$app->getRequest()->serverName : 'console';
        return $serverName . $key;
    }

    public static function getCacheDependency($modelName){
        $serverName = isset(\Yii::$app->getRequest()->serverName) ? \Yii::$app->getRequest()->serverName : 'console';
        return new TagDependency(['tags' => $serverName . $modelName]);
    }

    public static function flushCache($modelName){
        $serverName = isset(\Yii::$app->getRequest()->serverName) ? \Yii::$app->getRequest()->serverName : 'console';
        TagDependency::invalidate(\Yii::$app->cache, $serverName . $modelName);
    }

}