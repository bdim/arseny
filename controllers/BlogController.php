<?php

namespace app\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\debug\models\timeline\DataProvider;
use yii\filters\AccessControl;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use app\models\Blog;
use app\models\Files;

class BlogController extends Controller
{

    public $title;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];
    }

    public function actionIndex(){

        $dates = Blog::getDates();

        $provider = new ArrayDataProvider([
            'allModels' => $dates,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => [
                    'pub_date',
                ],
                'defaultOrder' => [
                    'pub_date' => SORT_DESC,
                ]
            ],
        ]);


        /*$query = Blog::find()->with('taxonomy');

        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => [
                    'id',
                    'publish_date',
                ],
                'defaultOrder' => [
                    'publish_date' => SORT_DESC,
                ]
            ],
        ]);*/

        return $this->render('bloglist', [
            'dataProvider' => $provider,
        ]);
    }

}
