<?php

namespace app\controllers;

use app\models\Blog;
use app\models\Log;
use app\models\Taxonomy;
use app\models\TelegramBot;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\debug\models\timeline\DataProvider;
use yii\filters\AccessControl;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\User;
use app\models\Files;
use app\models\SignupForm;
use yii\helpers\Url;
use app\components\VarDump;


class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
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

/*        if (Yii::$app->user->isGuest) {
            $this->redirect(Url::to(['site/login']));
	    } else*/
/*            if (Yii::$app->user->identity->role == User::ROLE_ADMIN)
            $this->redirect(Url::to(['user/list']));*/
/*        elseif (Yii::$app->user->identity->role == User::ROLE_USER)
            $this->redirect(Url::to(['site/call-list']));*/



        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goHome();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }


    public function actionAddadmin() {
        $model = User::find()->where(['username' => 'admin'])->one();
        if (empty($model)) {
            $user = new User();
            $user->username = 'admin';
            $user->fio = 'admin';
            $user->info = 'Administrator';
            $user->role = User::ROLE_ADMIN;
            $user->setPassword('000');
            $user->generateAuthKey();
            if ($user->save()) {
                echo 'good';
            }
        }
    }

   /* public function actionSignup()
    {
        $model = new SignupForm();
 
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }
 
        return $this->render('signup', [
            'model' => $model,
        ]);
    }*/


    public function actionImport(){
        die;
        $content = file_get_contents('../upload/node-export-4.export');
        $content = json_decode($content);

        $i=0;
        foreach($content as $node){
            if (empty($node->body->und[0]->value)){
                VarDumper::dump($node,10,1);die;
            }
            $item = [
                'created_at' => date('Y-m-d H:i:s',$node->created),
                'user_id' => $node->uid,
                'title' => $node->title,
                'body'  => $node->body->und[0]->value,
                'publish_date'  => !empty($node->field_date->und[0]->value) ? $node->field_date->und[0]->value : date('Y-m-d H:i:s',$node->created),
                'tag'  => $node->field_tag->und[0]->tid ? $node->field_tag->und[0]->tid : Taxonomy::TAG_ARSENY,
            ];
            $keyword = [];
            if (is_array($node->field_keyword->und))
            foreach($node->field_keyword->und as $kw){
                $keyword[] = $kw->tid;
            }

            Blog::add($item,$keyword);
            $i++;
        }
        echo "added ".$i;
	}

    public function actionImportfoto(){
        if (!Yii::$app->user->isGuest) {
            Files::importPhotoFromFolder('photo_jpg');
            Blog::flushCache();
            echo 'ok';
        }
    }

    public function  actionTest(){
        /*$key = 'test';
        $data = Yii::$app->cache->getOrSet($key, function () {
            return 'test '.date('H:i:s');
        }, 600);

        VarDumper::dump($data,10,1);*/

/*        $t = Taxonomy::getIdByName('прогулка');
        VarDumper::dump($t,10,1);*/

/*        $q = Yii::$app->db->createCommand('SELECT DATE(`publish_date`) FROM {{%blog}} GROUP BY DATE(`publish_date`) '
        )->execute();*/

        //VarDumper::dump(Yii::$app->user->identity,10,1);

       // TelegramBot::sendEventMessage();


    }

    public function actionFlushblog(){
        if (!Yii::$app->user->isGuest) {
            Blog::flushCache();
            echo 'Flushblog';
        }
    }
}
