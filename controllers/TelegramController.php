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
use yii\helpers\Url;
use aki\telegram;
use app\components\VarDump;
use app\models\TelegramBot;

class TelegramController extends Controller
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
                'only' => ['webhook'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        /*return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];*/
    }

    public function beforeAction($action)
    {
        if (in_array($action->id, ['webhook'])) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionSetwebhook()
    {
        $res = Yii::$app->telegram->setWebhook([
            'url' => 'https://arseny.su/telegram/webhook'
        ]);

        //file_put_contents('../../telegramlog.txt',date("Y-m-d H:i:s").' : '.json_encode($res));

    }

    /** @var telegram Yii::$app->telegram
     *
     * */
    public function actionWebhook(){
        $data = Yii::$app->telegram->hook();

        //TelegramBot::login($data);
        $bot = new TelegramBot($data);
        if (!$bot->executeCommands()){
            if (!$bot->executeCallbackQuery())
                $bot->processMessage();
        }
    }

    public function actionLogview(){
        $lines = file_get_contents('../../telegramlog.txt');

        $lines = explode("\n", $lines);
        foreach ($lines as $line) {
            $line_date = mb_substr($line,0,22);
            $line_json = mb_substr($line,22);

            echo $line_date. "<br>";
            VarDumper::dump(json_decode($line_json), 10, 1);
            echo "<br>";

        }
    }
}
