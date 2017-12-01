<?php

namespace app\controllers;

use app\models\Blog;
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
use app\models\Event;
use dosamigos\editable\EditableAction;

class EventController extends Controller
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
                        'actions' => ['list','update','add','delete'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return !Yii::$app->user->isGuest;
                        }
                    ],
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
                //'scenario' => 'editable',  //optional
                'modelClass' => Event::className(),
            ],
        ];
    }

    public function actionAdd(){
        $model = new Event();
        $model->save();
        $this->redirect(Url::to(["event/list"]));
    }

    public function actionDelete($id){
        Event::deleteAll(['id' => $id]);
        $this->redirect(Url::to(['event/list']));
    }

    public function actionList(){
        $query = Event::find()->orderBy("id DESC");

        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => [
                    'id',
                    'date_start',
                    'date_end',
                    'publish_date',
                ],
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);

        return $this->render('list', [
            'dataProvider' => $provider,
        ]);

    }

}
