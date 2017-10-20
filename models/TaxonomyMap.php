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
     * @property integer $blog_id
     */
    class TaxonomyMap extends ActiveRecord
    {

        /**
         * @inheritdoc
         */
        public static function tableName()
        {
            return '{{%taxonomy_map}}';
        }
     

        /**
         * @inheritdoc
         */
        public function rules()
        {
            return [
                [['blog_id', 'tid'], 'safe' ],
            ];
        }

        /**
         * @inheritdoc
         */
        public static function findIdentity($tid, $blog_id)
        {
            return static::findOne(['tid' => $tid,'blog_id' => $blog_id, ]);
        }

    }