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
    use \yii\caching\TagDependency;
    /**
     * Taxonomy model
     *
     * @property integer $tid
     * @property integer $vid
     * @property string $name
     * @property string $description
     * @property string $format
     * @property integer $weight
     * @property string $uuid
     */
    class Taxonomy extends ActiveRecord
    {
        const VID_KEYWORDS  = 5; // ключевые слова
        const VID_BLOG_TAG  = 1; // про кого пишем

        const TAG_ARSENY   = 2;
        const TAG_YAROSLAV = 8;

        const CACHE_DEPENDENCY_KEY = 'Taxonomy';

        public static $tag_case = [
            Taxonomy::TAG_ARSENY => [
                'и' => 'Арсений',
                'р' => 'Арсения',
            ],
            Taxonomy::TAG_YAROSLAV => [
                'и' => 'Ярослав',
                'р' => 'Ярослава',
            ],
        ];
        /**
         * @inheritdoc
         */
        public static function tableName()
        {
            return '{{%taxonomy_data}}';
        }
     

        /**
         * @inheritdoc
         */
        public function rules()
        {
            return [
                [['vid', 'name', 'description', 'format', 'weight', 'uuid'], 'safe' ],
            ];
        }


        /**
         * @inheritdoc
         */
        public static function findIdentity($id)
        {
            return static::findOne(['tid' => $id]);
        }


        /**
         * @inheritdoc
         */
        public function getId()
        {
            return $this->getPrimaryKey();
        }

        /**
         * @inheritdoc
         */
        public static function getIdByName($name, $vid = Taxonomy::VID_KEYWORDS)
        {
            $name = trim(mb_strtolower($name,'utf-8'));
            $tag =  static::findOne(['name' => $name, 'vid' => $vid]);

            if (!$tag){
                $tag = Taxonomy::addTag($name, $vid);
            }

            return $tag->tid;
        }

        /**
         * @inheritdoc
         */
        public static function getNameById($id)
        {
            $tag =  static::findOne(['tid' => $id]);

            $name = strval($tag->name);
            if ($tag->vid == Taxonomy::VID_BLOG_TAG)
                $name = StringUtils::mb_ucfirst($name);

            return $name;
        }


        public static function addTag($name, $vid = Taxonomy::VID_KEYWORDS){

            $tag = new Taxonomy();
            $tag->setAttributes([
                'vid' => $vid,
                'name'=> $name,
                'uuid'=> intval(Yii::$app->user->id)
            ]);
            $tag->save();

            return $tag;
        }

        /* кеш */
        public static function getCacheDependency(){
            return new TagDependency(['tags' => static::CACHE_DEPENDENCY_KEY]);
        }
        public static function flushCache(){
            TagDependency::invalidate(Yii::$app->cache, static::CACHE_DEPENDENCY_KEY);
        }

        public static function getVocabularyTags($vid){
            $result = Yii::$app->cache->getOrSet('vocabularyTags-'.$vid,function() use ($vid) {

                $res = static::find()->where('`vid` = :vid', [':vid' => $vid])->orderBy('`weight` ASC')->all();
                return $res;

            } ,3600*24, static::getCacheDependency());

            return $result;
        }
    }