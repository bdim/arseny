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

        public $tag;

        protected $_tagsIds = null;
        protected $_tagsNames = null;
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
            return $this->hasMany(TaxonomyMap::className(), ['blog_id' => 'id']);
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

            $this->updated_at = date("Y:m:d H:i:s");

            return parent::beforeSave($insert);
        }

        public function afterSave($insert, $changedAttributes){
            if (!empty($this->tag)){
                $tagId = Taxonomy::getIdByName($this->tag, Taxonomy::VID_BLOG_TAG);
                Yii::$app->db->createCommand('INSERT IGNORE into {{%taxonomy_map}} (`blog_id`,`tid` ) VALUES (:blog_id,:tid) ',
                    [
                        ':blog_id' => $this->id,
                        ':tid'     => $tagId,
                    ]
                )->execute();
            }

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




    }