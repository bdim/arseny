<?php
     
    namespace app\models;
     
    use Yii;
    use yii\base\Model;
    use app\models\User;
    use yii\helpers\VarDumper;

    //use yii\web\User;

    /**
     * Signup form
     */
    class SignupForm extends Model
    {
     
        public $username;
        public $fio;
        public $info;
        public $role;
        public $password;


        /**
         * @inheritdoc
         */
        public function rules()
        {
            return [
                ['fio', 'trim'],
                ['fio', 'required'],
                ['fio', 'string', 'min' => 2, 'max' => 255],
                ['role', 'string'],
                ['role', 'required'],
                ['info', 'string'],
                ['username', 'trim'],
                ['username', 'required'],
                ['username', 'string', 'max' => 255],
                ['username', 'unique', 'targetClass' => '\app\models\User', 'message' => 'This login has already been taken.', 'on' => ['create']],
                ['password', 'required', 'on' => ['create']],
                ['password', 'string', 'min' => 6],
            ];
        }

        public function scenarios()
        {
            $scenarios = parent::scenarios();
            $scenarios['add'] = ['username', 'fio','info','role','password'];
            $scenarios['update'] = ['username', 'fio','info','role','password'];

            return $scenarios;
        }

        public function attributeLabels()
        {
            return [
                'fio' => 'User name',
                'username' => 'Site login',
            ];
        }

        /**
         * Signs user up.
         *
         * @return User|null the saved model or null if saving fails
         */
        public function signup($user = null)
        {
     
            if (!$this->validate()) {
                return null;
            }

            if (empty($user))
                $user = new User();

            $user->username     = $this->username;
            $user->role         = $this->role ? $this->role :User::ROLE_USER;
            $user->fio          = $this->fio;
            $user->info         = $this->info;

            if (!empty($this->password)){
                $user->setPassword($this->password);
                $user->generateAuthKey();
            }

            return $user->save() ? $user : null;
        }

        public function update($user){
            return $this->signup($user);
        }
     
    }