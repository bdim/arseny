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

        protected $_tag; // Это про ког пишем, есть еще keywords - они отдельно
        protected $_tagsIds = null;
        protected $_tagsNames = null;

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

        /* relation Taxonomy-map */
        public function getTaxonomy()
        {
            $q =  $this->hasMany(TaxonomyMap::className(), ['blog_id' => 'id']);

            return $q;
        }

        /* id шники тегов*/
        public function getTagsIds(){
            if (is_null($this->_tagsIds)){
                $this->_tagsIds = [];
                foreach ($this->taxonomy as $tax){
                    $this->_tagsIds[] =$tax->tid;
                }
            }

            return $this->_tagsIds;
        }

        public function getTag(){
            return $this->getTagsIds();
        }
        public function setTag($tag){
            $this->tag = $tag;
        }

        public function getTagNames(){
            if (is_null($this->_tagsNames)){
                $this->_tagsNames = [];
                if (!empty($this->tagsIds))
                    foreach ($this->tagsIds as $id)
                        $this->_tagsNames[$id] = Taxonomy::getNameById($id);
            }

            return $this->_tagsNames;
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
            if (!empty($this->tag)){ // про кого пишем

                // удаляем все теги словаря VID_BLOG_TAG
                Yii::$app->db->createCommand('DELETE m.* FROM {{%taxonomy_map}} m LEFT JOIN {{%taxonomy_data}} t ON m.`tid` = t.`tid`
                                                  WHERE m.`blog_id` = :blog_id AND t.`vid` = :vid;',
                    [
                        ':blog_id' => $this->id,
                        ':vid'     => Taxonomy::VID_BLOG_TAG,
                    ]
                )->execute();

                if (!is_array($this->tag))
                    $this->tag = [$this->tag];

                foreach ($this->tag as $tag){
                    if (is_numeric($tag))
                        $tagId = $tag;
                    else
                        $tagId = Taxonomy::getIdByName($tag, Taxonomy::VID_BLOG_TAG);

                    Yii::$app->db->createCommand('INSERT IGNORE into {{%taxonomy_map}} (`blog_id`,`tid` ) VALUES (:blog_id,:tid) ',
                        [
                            ':blog_id' => $this->id,
                            ':tid'     => $tagId,
                        ]
                    )->execute();
                }
            }
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
                $blog->addKeywords($keywords);
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

        public function addKeywords($keywords){
            if (empty($keywords)) return;

            if (!is_array($keywords))
                $keywords = [$keywords];

            foreach ($keywords as $name)
                Yii::$app->db->createCommand('INSERT IGNORE into {{%taxonomy_map}} (`blog_id`,`tid` ) VALUES (:blog_id,:tid) ',
                    [
                        ':blog_id' => $this->id,
                        ':tid'     => is_numeric($name) ? $name : Taxonomy::getIdByName($name)
                    ]
                )->execute();
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

        /* кеш */
        public static function getCacheDependency(){
            return new TagDependency(['tags' => static::CACHE_DEPENDENCY_KEY]);
        }

        public static function flushCache(){
            TagDependency::invalidate(Yii::$app->cache, static::CACHE_DEPENDENCY_KEY);
        }

        /* массив дат с сообщениями и/или фотками */
        public static function getDates($filter=[]){

            $dates = Yii::$app->cache->getOrSet('blog-dates'.json_encode($filter),function() use ($filter) {
                $query = Blog::find()->select('DATE(`publish_date`) as pub_date')->where('(`title` <> "" OR `body` <> "" OR `photo` <> "")')->groupBy('pub_date')->all();
                $dates = [];
                foreach ($query as $q) {
                    if (empty($filter['year']) || (!empty($filter['year']) && mb_substr($q->pub_date,0,4) == $filter['year']))
                        $dates[$q->pub_date] = ['pub_date' => $q->pub_date, 'blog' => true];
                }
                $query = Files::find()->select('DATE(`date_id`) as pub_date')->groupBy('pub_date')->all();
                foreach ($query as $q) {
                    if (empty($filter['year']) || (!empty($filter['year']) && mb_substr($q->pub_date,0,4) == $filter['year']))
                        $dates[$q->pub_date] = ['pub_date' => $q->pub_date, 'files' => true];
                }
                ksort($dates);

                return $dates;
            } ,3600*24, static::getCacheDependency());

            return $dates;
        }

    }