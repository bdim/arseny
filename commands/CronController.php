<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use app\models\Files;
use app\models\Blog;
use app\models\TelegramBot;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CronController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     */
    public function actionIndex($message = 'test')
    {
        echo $message . "\n";
    }

    public function actionDailyNight(){
        /* import files */
        Files::importFilesFromFolder('photo_jpg', Files::TYPE_PHOTO);
        Files::removeNonExistFiles();

        Blog::clear();

        Blog::flushCache();
    }

    public function actionDailyDay(){
        /* рассылка в телеграм о событиях */
        TelegramBot::sendEventMessage();
    }
}
