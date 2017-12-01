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
     * @property integer $model_id
     * @property string $model_name
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
                [['model_id', 'model_name', 'tid'], 'safe' ],
            ];
        }

        /**
         * @inheritdoc
         */
        public static function findIdentity($tid, $model_id, $model_name = 'Blog')
        {
            return static::findOne(['tid' => $tid,'model_id' => $model_id,'model_name' => $model_name, ]);
        }

    }