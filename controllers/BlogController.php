<?php

namespace app\controllers;

use app\components\DateUtils;
use app\components\StringUtils;
use app\models\Taxonomy;
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
use dosamigos\editable\EditableAction;

class BlogController extends Controller
{

    public $title;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['@']
                    ],
                    [
                        'actions' => ['comparison'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return !Yii::$app->user->isGuest;
                        }
                    ]
                ],
        ]];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'update' => [
                'class' => EditableAction::className(),
                //'scenario' => 'editable',  //optional
                'modelClass' => Blog::className(),
            ],
        ];
    }

    public function actionIndex(){

        $filter['year']   = Yii::$app->request->get('year');
        $filter['tag']    = Yii::$app->request->get('tag');
        $filter['notags'] = Yii::$app->request->get('notags');

        $sort =  "SORT_". (Yii::$app->request->get('sort') ? Yii::$app->request->get('sort') : 'DESC');

        $dates = Blog::getDates($filter);

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
                    'pub_date' => constant($sort),
                ]
            ],
        ]);

        return $this->render('bloglist', [
            'dataProvider' => $provider,
            'model'        => $dates,
        ]);
    }


    public function actionComparison(){

        $filter['tag'] = Taxonomy::TAG_ARSENY;
        $dates[Taxonomy::TAG_ARSENY] = Blog::getDates($filter);

        $filter['tag'] = Taxonomy::TAG_YAROSLAV;
        $dates[Taxonomy::TAG_YAROSLAV] = Blog::getDates($filter);

        $dd = [];
        foreach ($dates[Taxonomy::TAG_ARSENY] as $date){
            $age = DateUtils::age("2012-05-12", $date['pub_date']);
            $date['age'] = DateUtils::age("2012-05-12", $date['pub_date'], true);
            $key = str_pad(intval($age['y']),2,'0', STR_PAD_LEFT).str_pad(intval($age['m']),2,'0', STR_PAD_LEFT).str_pad(intval($age['d']),2,'0', STR_PAD_LEFT);
            $dd[$key][Taxonomy::TAG_ARSENY] = $date;
        }
        foreach ($dates[Taxonomy::TAG_YAROSLAV] as $date){
            $age = DateUtils::age("2016-08-18", $date['pub_date']);
            $date['age'] = DateUtils::age("2016-08-18", $date['pub_date'], true);
            $key = str_pad(intval($age['y']),2,'0', STR_PAD_LEFT).str_pad(intval($age['m']),2,'0', STR_PAD_LEFT).str_pad(intval($age['d']),2,'0', STR_PAD_LEFT);
            $dd[$key][Taxonomy::TAG_YAROSLAV] = $date;
        }
        ksort($dd);

        $_dd = [];
        foreach($dd as $d){
            $age = $d[2]['age'] ? $d[2]['age'] : $d[8]['age'];
            $_dd[$age][] = $d;
        }
        $dd = $_dd;

        $sort =  "SORT_". (Yii::$app->request->get('sort') ? Yii::$app->request->get('sort') : 'ASC');

        $provider = new ArrayDataProvider([
            'allModels' => $dd,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => [
                    'pub_date',
                ],
                'defaultOrder' => [
                    'pub_date' => constant($sort),
                ]
            ],
        ]);

        return $this->render('comparison', [
            'dataProvider' => $provider,
            'model'        => $dates,
        ]);
    }
}
