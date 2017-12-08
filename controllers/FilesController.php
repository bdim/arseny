<?php

namespace app\controllers;

use dosamigos\editable\EditableAction;
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
use app\models\Files;

class FilesController extends Controller
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
                        'actions' => ['index','update'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return !Yii::$app->user->isGuest;
                        }
                    ]
                ],
            ]
        ];
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
                'modelClass' => Files::className(),
            ],
        ];
    }

    public function actionIndex(){

        $filter['date_id']   = Yii::$app->request->get('date_id');
        $filter['event_id']   = Yii::$app->request->get('event_id');

        $sort =  "SORT_". (Yii::$app->request->get('sort') ? Yii::$app->request->get('sort') : 'ASC');

        $query = Files::find();

        if ($filter['date_id'] ){
            $query->where("DATE(`date_id`) = :date_id", [':date_id' => $filter['date_id']] );
        }
        if ($filter['event_id'] ){
            $query->andWhere("`event_id` = :event_id", [':event_id' => $filter['event_id']] );
        }

        $query->orderBy('date_id ASC');


        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => [
                    'id',
                    'date_id',
                ],
                'defaultOrder' => [
                    'date_id' => constant($sort),
                ]
            ],
        ]);

        return $this->render('fileslist', [
            'dataProvider' => $provider,
            'filter' => $filter
        ]);
    }

}
