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
     * Files model
     *
     * @property integer $id
     * @property integer $type_id
     * @property string $path
     * @property string $caption
     * @property datetime $date_id
     */
    class Files extends ActiveRecord
    {

        const TYPE_PHOTO = 1;
        const TYPE_AUDIO = 2;
        const TYPE_VIDEO = 3;

        /**
         * @inheritdoc
         */
        public static function tableName()
        {
            return '{{%files}}';
        }
     

        /**
         * @inheritdoc
         */
        public function rules()
        {
            return [
                ['type_id' , 'required'],
                ['type_id' , 'integer'],
                ['path' , 'required'],
                ['path' , 'string'],
                ['caption' , 'string'],
                ['date_id', 'safe' ],
            ];
        }

        /**
         * @inheritdoc
         */
        public static function findIdentity($id)
        {
            return static::findOne(['id' => $id]);
        }

        public static function last($limit = 1){
            return static::find()->orderBy('id DESC')->limit($limit)->all();
        }

        public static function add($path, $type = 1, $caption ='', $date = null){
            $f = new Files();
            $f->setAttributes([
                'path'    => $path,
                'type_id' => $type,
                'caption' => $caption
            ]);
            if (!is_null($date))
                $f->date_id = $date;
            else
                $f->date_id = date('Y-m-d H:i:s');

            return $f->save();
        }
    }