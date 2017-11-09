<?php     	
    namespace app\models;
     
    use app\components\StringUtils;
    use Yii;
    use yii\base\NotSupportedException;
    use yii\behaviors\TimestampBehavior;
    use yii\db\ActiveRecord;
    use yii\helpers\Json;
    use yii\helpers\VarDumper;
    use yii\web\IdentityInterface;
    use yii\web\UrlManager;
    use app\models\Taxonomy;

    /**
     * Event model
     */
    class Event extends ActiveRecord
    {


        /**
         * @inheritdoc
         */
        public static function tableName()
        {
            return '{{%event}}';
        }
     

        /**
         * @inheritdoc
         */
        public function rules()
        {
            return [
                [['event_date', 'child_id', 'user_id', 'title', 'post_text'], 'safe' ],
            ];
        }

        /**
         * @inheritdoc
         */
        public static function findIdentity($id)
        {
            return static::findOne(['id' => $id]);
        }

        public static function postEvent(){
            $date = date('Y-m-d', time() - 24*3600);
            return static::findOne(['event_date' => $date]);
        }

        public function getMessage(){
            $message = 'Привет! ';
            $message .= $this->title ? 'Вчера у '.Taxonomy::$tag_case[$this->child_id]['р']. ' произошло событие: '.$this->title.'. ' . 'Есть что рассказать?'  : '';
            $message .= $this->post_text ? $this->post_text.'. ' : '';

            return $message;
        }

        /* relation User */
        public function getUser(){
            return $this->hasOne(User::className(), ['id' => 'user_id']);
        }
    }