<?php     	
    namespace app\models;
     
    use aki\telegram\Telegram;
    use app\components\StringUtils;
    use Yii;
    use yii\base\Model;
    use yii\base\NotSupportedException;
    use yii\helpers\Json;
    use yii\helpers\VarDumper;
    use yii\web\IdentityInterface;
    use yii\web\UrlManager;
    use app\models\User;

    class TelegramBot extends Model
    {

        const COMMAND_START     = '/start';
        const COMMAND_ADD_TEXT  = 'addText';
        const COMMAND_ADD_PHOTO = 'addPhoto';
        const COMMAND_LAST_BLOG = 'lastBlog';
        const COMMAND_LAST_FILES= 'lastFiles';

        public $chatId;
        public $data;

        protected $_user = null;
        protected $textCommands = [
            'новая запись' => TelegramBot::COMMAND_ADD_TEXT,
            'новое фото'   => TelegramBot::COMMAND_ADD_PHOTO,
            'последние записи'  => TelegramBot::COMMAND_LAST_BLOG,
            'последние файлы'   => TelegramBot::COMMAND_LAST_FILES,
        ];

        public function __construct($data){
            $this->data = $data;
            //Получаем chat_id
            if (isset($data->message))
                $this->chatId = $data->message->from->id;
            elseif (isset($data->callback_query))
                $this->chatId = $data->callback_query->from->id;

            $this->_user = User::findOne(['telegram_id' => $this->chatId]);

            $this->log([$this->data, $this->cachedCommand], null);
        }

        public function getUser(){
            if (empty($this->_user))
                $this->login();

            return $this->_user;
        }

        public function login(){
            if ($this->cachedCommand == TelegramBot::COMMAND_START){
                $token  = $this->data->message->text;
            } else
                return null;

            $response = [];
            if ($token && $this->_user = User::findOne(['auth_key' => $token])) {//сравниваем
                if ($this->_user->telegram_id) {
                    $response['text'] = "Уважаемый ".$this->_user->fio.", Вы уже авторизованы в системе. ";
                } else {
                    $this->_user->telegram_id = $this->chatId; //сохраняем chat_id в бд
                    $this->_user->save();
                    $response['text'] = "Добро пожаловать, ".$this->_user->fio.". Вы успешно авторизовались!";
                }
            } else {
                $response['text'] = "Извините, не удалось найти данный токен!";
            }

            $this->sendMessage($response);

        }

        /* кеш */
        protected function getKey($subkey = 'command'){
            return 'telegram-'.$subkey.'-'.$this->chatId;
        }

        protected function setCache($subkey, $value){
            return Yii::$app->cache->set($this->getKey($subkey), $value);
        }

        protected function getCache($subkey){
            return Yii::$app->cache->get($this->getKey($subkey));
        }

        protected function clearCache($subkey){
            Yii::$app->cache->set($this->getKey($subkey), null);
        }

        public function flushCache(){
            $this->clearCommandCache();
            $this->clearBlogCache();
        }

        protected function clearBlogCache(){
            $this->clearCache('text');
            $this->clearCache('hash');
            $this->clearCache('blog_id');
            $this->clearCache('tag');
        }

        protected function getCachedCommand(){
            return Yii::$app->cache->get($this->key);
        }

        protected function clearCommandCache(){
            Yii::$app->cache->set($this->key, null);
        }

        /* commands */
        protected function getCommands(){
            $commands = [];

            // команды бота "/..."
            if (!empty($this->data->message->entities))
                foreach($this->data->message->entities as $item){
                    if ($item->type == 'bot_command'){
                        $commands[] = mb_substr($this->data->message->text, $item->offset, $item->length);
                    }
                }

            // текстовые команды
            if (empty($commands)){
                $text = trim(mb_strtolower($this->data->message->text));
                if (!empty($this->textCommands[$text]))
                    $commands[] = $this->textCommands[$text];
            }

            return $commands;
        }

        public function executeCommands($commands = []){
            if (empty($commands))
                $commands = $this->getCommands($this->data);

            if (!count($commands))
                return false;

            foreach ($commands as $command){

                // запоминаем текущую команду
                Yii::$app->cache->set($this->key, $command);

                $action = 'command'.StringUtils::mb_ucfirst(str_replace("/","",$command));

                if (method_exists(get_called_class(),$action)){
                    $response = $this->$action();
                    if ($response)
                        $this->sendMessage($response);
                }
            }

            return true;
        }

        /* callback_query */
        public function executeCallbackQuery(){
            if (!empty($this->data->callback_query)){
                $s = explode("::",$this->data->callback_query->data);
                $action = $s[0];
                $params = $s[1];

                if (method_exists(get_called_class(),$action)){
                    $response = $this->$action($params);
                    if ($response)
                        $this->sendCallback($response);
                }

                return true;
            }
             else
                 return false;
        }


        /* обработка текста после команды */
        public function processMessage(){

            /* /start */
            if ($this->cachedCommand == TelegramBot::COMMAND_START){
                $this->login();
                if (!empty($this->_user))
                    $this->sendNewButton();
            }

            /* проверка авторизации */
            if (empty($this->_user))
                return;

            /* Новая запись */
            if ($this->cachedCommand == TelegramBot::COMMAND_ADD_TEXT){
                if (!empty($this->data->message->text)){

                    $text = "<p>".$this->data->message->text."</p>";

                    if ($this->getCache('text') != $text){

                        $blog_id = $this->getCache('blog_id');
                        if (!empty($blog_id)){

                            Blog::insertText($blog_id, $text);

                        } else {
                            $item = [
                                'created_at'    => date('Y-m-d H:i:s'),
                                'user_id'       => $this->user->id,
                                'title'         => '',
                                'body'          => $text,
                                'publish_date'  => date('Y-m-d H:i:s'),
                            ];
                            $keyword = $this->getCache('tag');
                            $keyword[] = 'telegram';

                            $blog_id = Blog::add($item,$keyword);
                        }

                        $this->setCache('text', $text);
                        $this->setCache('blog_id', $blog_id);

                        $response['text'] = 'записал';
                        $this->sendMessage($response);
                    }
                    return true;
                }
            }

            /* загрузка фото */
            if ($this->cachedCommand == TelegramBot::COMMAND_ADD_PHOTO){
                if (!empty($this->data->message->photo)){
                    $fileid = '';
                    foreach ($this->data->message->photo as $photoSize){
                        if ($photoSize->file_size < 100000)
                            $fileid = $photoSize->file_id;
                    }
                    if (empty($fileid))
                        $fileid = $this->data->message->photo[0]->file_id;

                    $photo = Yii::$app->telegram->getFile([
                        'file_id' => $fileid
                    ]);

                    $caption = $this->data->message->caption;

                    if (!empty($photo)){
                        $this->log([$photo]);

                        if ($photo->ok){
                            $filename = $this->downloadPhoto($photo);
                            if ($filename){
                                Files::add($filename, Files::TYPE_PHOTO, $caption);
                            }
                        }
                    }
                }
                return true;
            }
            if ($this->cachedCommand == TelegramBot::COMMAND_LAST_BLOG){
                if (!empty($this->data->message->text) && is_numeric(($this->data->message->text))){
                    return $this->commandLastBlog(intval($this->data->message->text));
                }
            }

            if ($this->cachedCommand == TelegramBot::COMMAND_LAST_FILES){
                if (!empty($this->data->message->text) && is_numeric(($this->data->message->text))){
                    return $this->commandLastFiles(intval($this->data->message->text));
                }
            }

            $this->clearCommandCache();
        }

        public function downloadPhoto($getFile){

            if (!empty($getFile->result->file_path)){
                $data = file_get_contents('https://api.telegram.org/file/bot'.Yii::$app->telegram->botToken.'/'.$getFile->result->file_path);

                $ext = trim(mb_strtolower(end(explode(".", $getFile->result->file_path))));
                if (in_array($ext, ['jpg','png','gif','tif'])){
                    $filename = date("Y-m-d-H-i-s")."-".$this->user->id."-telegram.".$ext;
                    if (file_put_contents('../upload/photo/'.$filename, $data, FILE_BINARY))
                        return 'photo/'.$filename;
                }
            }
            return false;
        }

        /* Sending ... */
        protected function sendMessage($response){

            if (!empty($response)){
                $response['chat_id'] = $this->chatId;
                $res = Yii::$app->telegram->sendMessage($response);
                $this->log([$response, $res]);
            }
        }

        protected function sendCallback($response){
            if (!empty($response && !empty($this->data->callback_query->id))){

                $response['callback_query_id'] = $this->data->callback_query->id;
                $res = Yii::$app->telegram->answerCallbackQuery($response);
                $this->log([$response, $res]);
            }
        }

        protected function sendNewButton(){
            $response['text'] = 'Вот тебе кнопки:';
            $response['reply_markup'] = json_encode([
                'keyboard'=>[
                    [
                        ['text'=>"Новая запись"],
                        ['text'=>"Новое фото"],
                        ['text'=>"Последние записи"],
                        ['text'=>"Последние файлы"],
                    ]
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
                'selective' => true
            ]);

            $this->sendMessage($response);
        }

        /* Log */
        protected function log($array, $appendType = FILE_APPEND){
            file_put_contents('../../telegramlog.txt',date("Y-m-d H:i:s").' : '.json_encode($array) ."\n",$appendType);
        }




        /* КОМАНДЫ */
        protected function commandStart(){
            $response['text'] = "Привет, ".$this->data->message->from->first_name.'! У вас есть токен?';
            return $response;
        }

        protected function commandInit(){
            $this->sendNewButton();
        }

        protected function commandAddText(){

            $response['text'] = "Про кого будем писать?";

            $response['reply_markup'] = json_encode([
                'inline_keyboard'=>[
                    [
                        ['text'=>"Арсений",'callback_data'=> 'callback_addTag::'.Taxonomy::TAG_ARSENY]
                    ],
                    [
                        ['text'=>"Ярослав",'callback_data'=> 'callback_addTag::'.Taxonomy::TAG_YAROSLAV]
                    ],
                    [
                        ['text'=>"Про обоих",'callback_data'=> 'callback_addTag::'.Taxonomy::TAG_ARSENY.','.Taxonomy::TAG_YAROSLAV]
                    ],
                ],
            ]);

            $this->clearBlogCache();
            $this->setCache('hash', time());

            return $response;
        }

        protected function commandAddPhoto(){
            $this->clearBlogCache();
            $response['text'] = "загрузите фото, не забудте подписать его";
            return $response;
        }

        protected function commandLastBlog($limit = 3){
            $blog = array_reverse(Blog::last($limit));

            foreach ($blog as $item){
                $response['text'] = Yii::$app->formatter->asDate($item->publish_date) ." ". strip_tags(html_entity_decode($item->body));

                /*$response['reply_markup'] = json_encode([
                    'inline_keyboard'=>[
                        [
                            ['text'=>"редактировать",'callback_data'=> 'callback_edit::'.$item->id, 'switch_inline_query_current_chat' => $item->body]
                        ],
                        [
                            ['text'=>"удалить",'callback_data'=> 'callback_delete::'.$item->id]
                        ]
                    ],
                ]);*/

                $this->sendMessage($response);
            }
        }

        protected function commandLastFiles($limit = 1){
            $files = Files::last($limit);
            $chatId = $this->data->message->from->id;//Получаем chat_id

            foreach($files as $file)
                Yii::$app->telegram->sendPhoto([
                    'chat_id' => $chatId,
                    'photo' => "../upload/".$file->path
                ]);
        }

        protected function commandStop(){
            $response['text'] = "пока!";
            $response['reply_markup'] = json_encode([
                'hide_keyboard'=> true
            ]);

            $this->flushCache();
            return $response;
        }

        protected function commandPhoto(){

            $chatId = $this->data->message->from->id;//Получаем chat_id

            Yii::$app->telegram->sendPhoto([
                'chat_id' => $chatId,
                'photo' => "./kino.jpg"
            ]);
        }






        /* CallBackQuery */
        protected function callback_addTag($param){
            $tagsName = [];
            $ids = explode(",",$param);
            foreach ($ids as $id)
                $tagsName[] = Taxonomy::$tag_case[$id]['р'];

            $this->setCache('tag', $ids);

            $response['text'] = 'пишем про '.implode(" и ",$tagsName);

            return $response;
        }

        protected function callback_edit($param){
            /*$response['text'] = 'редактируем запись '.$param;

            return $response;*/

            Yii::$app->telegram->editMessageText([
               'chat_id' => $this->chatId, //Optional
               'message_id' => $this->data->callback_query->message->message_id, //Optional
               //'inline_message_id' => 'my alert',  //Optional
               'text' => $this->data->callback_query->message->text, //require
               /*'parse_mode' => 123231,  //Optional
               'disable_web_page_preview' => false or true,  //Optional
               'reply_markup' => Type InlineKeyboardMarkup,  //Optional*/
            ]);

        }
    }