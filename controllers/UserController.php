<?php

namespace app\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\debug\models\timeline\DataProvider;
use yii\filters\AccessControl;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\User;
use app\models\SignupForm;
use yii\helpers\Url;
use app\components\VarDump;

class UserController extends Controller
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
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['list','add','update','delete'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return User::isUserAdmin(Yii::$app->user->identity);
                        }
                    ],
                    [
                        'actions' => ['view'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->role == User::ROLE_USER;
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
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
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        /*return $this->actionList();*/
    }



    public function actionList(){

        $query = User::find()->where([/*'role' => User::ROLE_USER,*/ 'status' => User::STATUS_ACTIVE]);

        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => [
                    'id',
                    'username',
                    'fio',
                ],
                'defaultOrder' => [
                    'id' => SORT_ASC,
                ]
            ],
        ]);

        return $this->render('userlist', [
            'dataProvider' => $provider,
        ]);
    }

    public function actionAdd(){
        $this->title = 'Добавить';

        $model = new SignupForm();
        $model->setScenario('create');

        if ($model->load(Yii::$app->request->post())) {

            if ($user = $model->signup()) {
                return $this->redirect(Url::to(['user/list']));
            }
        }

        return $this->render('signup', [
            'model' => $model,
            'title' => $this->title,
        ]);
    }

    public function actionUpdate($id){
        $this->title = 'Изменить';

        $user = User::findById($id);

        $model = new SignupForm();
        $model->setScenario('update');

        if ($model->load(Yii::$app->request->post())) {
            if ($model->update($user)) {
                return $this->redirect(Url::to(['user/list']));
            }
        } else
            $model->setAttributes($user->attributes);

        return $this->render('signup', [
            'model' => $model,
            'title' => $this->title,
        ]);
	}

    public function actionDelete($id){
        User::deleteAll(['id' => $id]);
        $this->redirect(Url::to(['user/list']));
	}


    public function actionView($id=0){
        $user = User::findById($id);

        if (!empty($user)){

        }
    }
}
