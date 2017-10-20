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
        const VID_KEYWORDS  = 5;
        const VID_BLOG_TAG  = 1;

        const TAG_ARSENY   = 2;
        const TAG_YAROSLAV = 8;

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


    }