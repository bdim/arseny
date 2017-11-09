<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\helpers\VarDumper;
use yii\rest\ActiveController;


class ApiController extends ActiveController
{

    public $modelClass = 'app\models\Event';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        /* API посредством ajax-запросов с других доменов */
        $behaviors['corsFilter' ] = [
            'class' => \yii\filters\Cors::className(),
        ];

        /* только формат JSON */
        $behaviors['contentNegotiator'] = [
            'class' => \yii\filters\ContentNegotiator::className(),
            'formats' => [
                'application/json' => \yii\web\Response::FORMAT_JSON,
            ],
        ];

        /* права доступов к действиям контроллера API */
        $behaviors['access'] = [
            'class' => \yii\filters\AccessControl::className(),
            'only' => ['create', 'update', 'delete'],
            'rules' => [
                [
                    'actions' => ['create', 'update', 'delete'],
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ],
        ];
        return $behaviors;
    }



}
