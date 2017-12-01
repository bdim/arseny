<?php     	
    namespace app\models;
     
    use app\components\StringUtils;
    use app\components\TaxonomyBehavior;
    use Yii;
    use yii\base\NotSupportedException;
    use yii\behaviors\TimestampBehavior;
    use yii\db\ActiveRecord;
    use yii\helpers\Json;
    use yii\helpers\VarDumper;
    use yii\web\IdentityInterface;
    use yii\web\UrlManager;
    use app\models\Taxonomy;
    use yii\caching\TagDependency;

    /**
     * Event model
     */
    class Event extends ActiveRecord
    {

        public $pub_date;

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
                [['date_start', 'date_end', 'publish_date', 'user_id', 'title', 'body', 'post_text'], 'safe' ],
            ];
        }

        /**
         * @inheritdoc
         */
        public function behaviors()
        {
            return [
                TaxonomyBehavior::className()
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
            return static::findOne(['date_end' => $date]);
        }

        public function getMessage(){
            $message = $this->post_text ? 'Привет! '.$this->post_text.'. ' : '';
            return $message;
        }

        /* relation User */
        public function getUser(){
            return $this->hasOne(User::className(), ['id' => 'user_id']);
        }


        /* кеш */
        public static function getCacheDependency(){
            return new TagDependency(['tags' => Blog::CACHE_DEPENDENCY_KEY]);
        }

        public static function flushCache(){
            TagDependency::invalidate(Yii::$app->cache, Blog::CACHE_DEPENDENCY_KEY);
        }

        public static function getItemsForDay($date){
            $items = Yii::$app->cache->getOrSet('event-for-date-'.$date, function() use ($date) {
                $query = Event::find()->where('DATE(`publish_date`) = :date' , [':date' => $date])->orderBy('publish_date')->all();
                return $query;
            } ,3600*24, static::getCacheDependency());

            return $items;
        }
    }