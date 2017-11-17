<?php     	
    namespace app\models;
     
    use app\components\StringUtils;
    use Yii;
    use yii\base\NotSupportedException;
    use yii\behaviors\TimestampBehavior;
    use yii\db\ActiveRecord;
    use yii\helpers\Json;
    use yii\helpers\VarDumper;
    use yii\web\UrlManager;


    /**
     * Log model
     */
    class Log extends ActiveRecord
    {


        /**
         * @inheritdoc
         */
        public static function tableName()
        {
            return '{{%log}}';
        }
     

        /**
         * @inheritdoc
         */
        public function rules()
        {
            return [
                [['date_id', 'message', 'context'], 'safe' ],
            ];
        }


        public static function add($params){
            $params['date_id'] = date("Y-m-d- H:i:s");

            $log = new Log();
            $log->setAttributes($params);
            $log->save();
        }


    }