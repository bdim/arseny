<?php     	
    namespace app\models;
     
    use Yii;
    use yii\base\NotSupportedException;
    use yii\behaviors\TimestampBehavior;
    use yii\db\ActiveRecord;
    use yii\helpers\Json;
    use yii\helpers\VarDumper;
    use yii\web\IdentityInterface;
    use yii\web\UrlManager;
    use \yii\caching\TagDependency;

    /**
     * Article model
     *
     * @property integer $id
     * @property string $created_at
     * @property string $updated_at
     * @property string $publish_date
     * @property integer $user_id
     * @property string $title
     * @property string $body
     * @property string $photo
     */
    class Article extends ActiveRecord
    {

        public $_tag; // Это про ког пишем, есть еще keywords - они отдельно
        public $_tagsIds = null;
        public $_tagsNames = null;

        public $tag;
        public $pub_date;

        const CACHE_DEPENDENCY_KEY = 'article';

        /**
         * @inheritdoc
         */
        public static function tableName()
        {
            return '{{%article}}';
        }
     
        /**
         * @inheritdoc
         */
        /*public function behaviors()
        {
            return [
                TimestampBehavior::className(),
            ];
        }*/

        /**
         * @inheritdoc
         */
        public function rules()
        {
            return [
                ['user_id','integer'],
                [['created_at','updated_at', 'publish_date', 'user_id', 'title', 'body', 'photo', 'tag'], 'safe' ],
            ];
        }


        public function beforeSave($insert){

            $this->updated_at = date("Y:m:d H:i:s");

            return parent::beforeSave($insert);
        }

/*        public function afterSave($insert, $changedAttributes){
            return parent::afterSave($insert, $changedAttributes);
        }*/

        /**
         * @inheritdoc
         */
        public static function findIdentity($id)
        {
            return static::findOne(['id' => $id]);
        }

        /**
         * @inheritdoc
         */
        public function getId()
        {
            return $this->getPrimaryKey();
        }

        public static function add($attributes, $keywords = []){
            $article = new Article();

            $article->setAttributes($attributes);
            if ($article->save()){
                $article->addKeywords($keywords);
                return $article->id;
            }
        }

        /* добавить текст в существующую запись */
        public static function insertText($id, $text){
            $article = article::findIdentity($id);

            if (!empty($article)){
                $article->body .= $text;
                $article->save();
            }
        }


        public static function getItemsForDay($date){
            $article = Yii::$app->cache->getOrSet('article-for-date-'.$date, function() use ($date) {
                $query = Article::find()->where('DATE(`publish_date`) = :date' , [':date' => $date])->orderBy('publish_date')->all();
                return $query;
            } ,3600*24, static::getCacheDependency());

            return $article;
        }

        /* кеш */
        public static function getCacheDependency(){
            return new TagDependency(['tags' => static::CACHE_DEPENDENCY_KEY]);
        }

        public static function flushCache(){
            TagDependency::invalidate(Yii::$app->cache, static::CACHE_DEPENDENCY_KEY);
        }

        /* массив дат с сообщениями и/или фотками */
        public static function getDates(){

            $dates = Yii::$app->cache->getOrSet('article-dates',function() {
                $query = Article::find()->select('DATE(`publish_date`) as pub_date')->groupBy('pub_date')->all();
                $dates = [];
                foreach ($query as $q) {
                    $dates[$q->pub_date] = ['pub_date' => $q->pub_date, 'article' => true];
                }
                $query = Files::find()->select('DATE(`date_id`) as pub_date')->groupBy('pub_date')->all();
                foreach ($query as $q) {
                    $dates[$q->pub_date] = ['pub_date' => $q->pub_date, 'files' => true];
                }
                ksort($dates);

                return $dates;
            } ,3600*24, static::getCacheDependency());

            return $dates;
        }

        /* id шники тегов*/
        public function getTagsIds(){
            return Taxonomy::getTagsIds($this);
        }

        public function getTag(){
            return $this->getTagsIds();
        }
        public function setTag($tag){
            $this->tag = $tag;
        }

        public function getTagNames(){
            return Taxonomy::getTagNames($this);
        }

    }