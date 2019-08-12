<?php     	
    namespace app\models;
     
    use app\components\RenderCache;
    use Yii;
    use yii\base\NotSupportedException;
    use yii\behaviors\TimestampBehavior;
    use yii\db\ActiveRecord;
    use yii\helpers\Json;
    use yii\helpers\VarDumper;
    use yii\web\IdentityInterface;
    use yii\web\UrlManager;
    use \yii\caching\TagDependency;
    use app\components\TaxonomyBehavior;

    /**
     * Blog model
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
    class Blog extends ActiveRecord
    {

        public $pub_date;


        const CACHE_DEPENDENCY_KEY = 'blog';

        /**
         * @inheritdoc
         */
        public static function tableName()
        {
            return '{{%blog}}';
        }
     
        /**
         * @inheritdoc
         */
        public function behaviors()
        {
            return [
                TaxonomyBehavior::className()
                //TimestampBehavior::className(),
            ];
        }

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

            if (empty($this->user_id))
                $this->user_id = Yii::$app->user->id;

            if ($this->isNewRecord)
                $this->created_at = date("Y:m:d H:i:s");

            if (is_null($this->publish_date))
                $this->publish_date = date("Y:m:d H:i:s");

            $this->updated_at = date("Y:m:d H:i:s");

            return parent::beforeSave($insert);
        }

        public function afterSave($insert, $changedAttributes){
            Blog::flushCache();
            return parent::afterSave($insert, $changedAttributes);
        }

        /**
         * @inheritdoc
         */
        public static function findIdentity($id)
        {
            return static::findOne(['id' => $id]);
        }

        public static function last($limit = 3){
            return static::find()->orderBy('id DESC')->limit($limit)->all();
        }

        /**
         * @inheritdoc
         */
        public function getId()
        {
            return $this->getPrimaryKey();
        }

        public static function add($attributes, $keywords = []){
            $blog = new Blog();

            $blog->setAttributes($attributes);
            if ($blog->save()){
                $blog->attachTag($keywords);
                return $blog->id;
            }
        }

        /* добавить текст в существующую запись */
        public static function insertText($id, $text){
            $blog = Blog::findIdentity($id);

            if (!empty($blog)){
                $blog->body .= $text;
                $blog->save();
            }
        }


        public function getIsEmpty(){
            return (empty($this->title) && empty($this->body) && empty($this->photo));
        }

        public static function getItemsForDay($date){
            $blog = Yii::$app->cache->getOrSet('blog-for-date-'.$date, function() use ($date) {
                $query = Blog::find()->where('DATE(`publish_date`) = :date' , [':date' => $date])->orderBy('publish_date')->all();
                return $query;
            } ,3600*24, static::getCacheDependency());

            return $blog;
        }

        public static function getLastDate(){
            $blog = Yii::$app->cache->getOrSet('blog-last-date-', function() {
                $query = Blog::find()->orderBy('publish_date DESC')->one();
                return $query;
            } ,3600*24, static::getCacheDependency());

            return mb_substr($blog->publish_date,0,10);
        }


        /* кеш */
        public static function getCacheDependency(){
            return RenderCache::getCacheDependency(static::CACHE_DEPENDENCY_KEY);
        }

        public static function flushCache(){
            RenderCache::flushCache(static::CACHE_DEPENDENCY_KEY);
        }

        /* массив дат с сообщениями и/или фотками */
        public static function getDates($filter=[]){

            $dates = Yii::$app->cache->getOrSet('blog-dates'.json_encode($filter),function() use ($filter) {
                $blogIds = [];
                $dates = [];
                $in = 'IN';
                if (!empty($filter['tag'])){
                    $blogIds = [
                        'Blog'  => [0],
                        'Event' => [0],
                        'Files' => [0],
                    ];
                    $nodes = TaxonomyMap::find()->select(['model_id', 'model_name'])->where("`tid` = :tid ", [':tid' => $filter['tag']])->all();
                    foreach ($nodes as $node)
                        $blogIds[$node->model_name][] = $node->model_id;
                } elseif (!empty($filter['notags'])){
                    $blogIds = [
                        'Blog'  => [0],
                        'Event' => [0],
                        'Files' => [0],
                    ];
                    $nodes = TaxonomyMap::find()->select(['model_id', 'model_name'])->all();
                    foreach ($nodes as $node)
                        $blogIds[$node->model_name][] = $node->model_id;

                }
                /* Blog */
                $query = Blog::find()->select('DATE(`publish_date`) as pub_date');
                if (!empty($filter['notags'])){
                    $query->where(" `id` ".$in." (".implode(',',$blogIds['Blog']).")");
                    //$query->orWhere('(`title` <> "" OR `body` <> "" OR `photo` <> "")')->groupBy('pub_date');
                }
                elseif (!empty($blogIds['Blog'])){
                    $query->where(" `id` ".$in." (".implode(',',$blogIds['Blog']).")");
                } else {
                    $query->where('(`title` <> "" OR `body` <> "" OR `photo` <> "")')->groupBy('pub_date');
                }


                $query = $query->all();

                foreach ($query as $q) {
                    if (empty($filter['year']) || (!empty($filter['year']) && mb_substr($q->pub_date,0,4) == $filter['year']))
                        $dates[$q->pub_date] = ['pub_date' => $q->pub_date, 'blog' => true];
                }

                /* Events */
                $query = Event::find()->select('DATE(`publish_date`) as pub_date');
                if (!empty($blogIds['Event'])){
                    $query->where(" `id` ".$in." (".implode(',',$blogIds['Event']).")");
                } else {
                    $query->where('(`title` <> "" OR `body` <> "")')->groupBy('pub_date');
                }

                $query = $query->all();

                foreach ($query as $q) {
                    if (empty($filter['year']) || (!empty($filter['year']) && mb_substr($q->pub_date,0,4) == $filter['year']))
                        $dates[$q->pub_date] = ['pub_date' => $q->pub_date, 'Event' => true];
                }

                /* теги прикреплены только к блогу */
                $textDates = $dates;
                $notagsDates = [];
                if (empty($filter['tag'])){
                    /* Files */
                    $query = Files::find()->select('DATE(`date_id`) as pub_date, event_id')->groupBy('pub_date')
                        ->where('event_id = 0')
                        ->all();

                    foreach ($query as $q) {
                        if (empty($filter['year']) || (!empty($filter['year']) && mb_substr($q->pub_date, 0, 4) == $filter['year'])){

                            if (!empty($filter['notags'])){
                                if ($q->event_id == 0 && empty($textDates[$q->pub_date]))
                                    $notagsDates[$q->pub_date] = ['pub_date' => $q->pub_date, 'files' => true];

                            } else
                                $dates[$q->pub_date] = ['pub_date' => $q->pub_date, 'files' => true];
                        }
                    }
                }
                if (!empty($filter['notags'])){
                    $dates = $notagsDates;
                }
                ksort($dates);

                return $dates;
            } ,3600*24, static::getCacheDependency());

            return $dates;
        }

        /* очистка от ненужных записей */
        public static function clear(){
            Yii::$app->db->createCommand('DELETE b.* FROM {{%blog}} b
                                            LEFT JOIN {{%files}} f ON DATE(b.`publish_date`) = DATE(f.`date_id`) AND f.`event_id` = 0
                                            WHERE b.`title` = "" AND b.`body` = "" AND b.`photo` = "" AND f.id IS NULL '
            )->execute();
        }
    }