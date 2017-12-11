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
     * User model
     *
     * @property integer $id
     * @property string $fio
     * @property string $username
     * @property string $role
     * @property string $info
     * @property string $password_hash
     * @property string $password_reset_token
     * @property string $auth_key
     * @property integer $status
     * @property integer $created_at
     * @property integer $updated_at
     * @property string $password write-only password
     * @property string $telegram_id
     */
    class User extends ActiveRecord implements IdentityInterface
    {
        const STATUS_DELETED = 0;
        const STATUS_ACTIVE = 10;

        const ROLE_ADMIN  = 'admin';
        const ROLE_EDITOR = 'editor';
        const ROLE_USER   = 'user';

        /**
         * @inheritdoc
         */
        public static function tableName()
        {
            return '{{%user}}';
        }
     
        /**
         * @inheritdoc
         */
        public function behaviors()
        {
            return [
                TimestampBehavior::className(),
            ];
        }

        /**
         * @inheritdoc
         */
        public function rules()
        {
            return [
                ['status', 'default', 'value' => self::STATUS_ACTIVE],
                ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
                ['role', 'in', 'range' => [self::ROLE_USER, self::ROLE_EDITOR,  self::ROLE_ADMIN]],
                ['telegram_id', 'safe']
            ];
        }

        public static function isUserAdmin($user = null)
        {
            if (is_null($user))
                $user = Yii::$app->user->identity;

            if ($user->role == User::ROLE_ADMIN)
            {
                return true;
            } else {
                return false;
            }
        }

        public static function isUserEditor($user = null)
        {
            if (is_null($user))
                $user = Yii::$app->user->identity;

            if ($user->role == User::ROLE_EDITOR || $user->role == User::ROLE_ADMIN)
            {
                return true;
            } else {
                return false;
            }
        }

        /**
         * @inheritdoc
         */
        public static function findIdentity($id)
        {
            return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
        }
     
        /**
         * @inheritdoc
         */
        public static function findIdentityByAccessToken($token, $type = null)
        {
            throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
        }
     
        /**
         * Finds user by username
         *
         * @param string $username
         * @return static|null
         */
        public static function findByUsername($username)
        {
            return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
        }

        public static function findById($id)
        {
            return static::findOne(['id' => $id]);
        }


        public static function findByRole($role)
        {
            return static::findAll(['role' => $role, 'status' => self::STATUS_ACTIVE]);
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
        public function getAuthKey()
        {
            return $this->auth_key;
        }
     
        /**
         * @inheritdoc
         */
        public function validateAuthKey($authKey)
        {
            return $this->getAuthKey() === $authKey;
        }
     
        /**
         * Validates password
         *
         * @param string $password password to validate
         * @return bool if password provided is valid for current user
         */
        public function validatePassword($password)
        {
            return Yii::$app->security->validatePassword($password, $this->password_hash);
        }
     
        /**
         * Generates password hash from password and sets it to the model
         *
         * @param string $password
         */
        public function setPassword($password)
        {
            $this->password_hash = Yii::$app->security->generatePasswordHash($password);
        }
     
        /**
         * Generates "remember me" authentication key
         */
        public function generateAuthKey()
        {
            $this->auth_key = Yii::$app->security->generateRandomString();
        }

        public function getEditUrl(){
            return '';
        }
    }