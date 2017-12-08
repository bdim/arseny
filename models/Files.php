<?php     	
    namespace app\models;
     
    use app\components\StringUtils;
    use app\components\TaxonomyBehavior;
    use Yii;
    use yii\base\NotSupportedException;
    use yii\behaviors\TimestampBehavior;
    use yii\db\ActiveRecord;
    use yii\helpers\Json;
    use yii\helpers\VarDumper;
    use yii\web\IdentityInterface;
    use yii\web\UrlManager;
    use app\components\ImageResizer;

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

        public $pub_date;

        protected $_params;

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
                ['event_id' , 'integer'],
                ['path' , 'unique'],
                ['path' , 'required'],
                ['path' , 'string'],
                ['caption' , 'string'],
                ['params' , 'string'],
                ['date_id', 'safe' ],
            ];
        }

        /**
         * @inheritdoc
         */
        public function behaviors()
        {
            return [
                TaxonomyBehavior::className()
            ];
        }

        public function beforeSave($insert){
            if (!empty($this->_params))
                $this->params = Json::encode($this->_params);

            return parent::beforeSave($insert);
        }

        public function afterSave($insert, $changedAttributes){
            Blog::flushCache();
            return parent::afterSave($insert, $changedAttributes);
        }

        public function afterFind(){
            if (!empty($this->params))
                $this->_params = Json::decode($this->params);
        }


        public function getParam(){
            return $this->_params;
        }

        public function setParam($key, $val){
            $this->_params[$key] = $val;
        }

        /**
         * @inheritdoc
         */
        public static function findIdentity($id)
        {
            return static::findOne(['id' => $id]);
        }

        public static function findbyPath($path)
        {
            return static::findOne(['path' => $path]);
        }

        public static function last($limit = 1){
            return static::find()->orderBy('id DESC')->limit($limit)->all();
        }

        /* перемещение файла */
        public static function renameFile($sourse, $distination){

            $path_parts = pathinfo($distination);

            if (!file_exists($path_parts['dirname']))
                mkdir($path_parts['dirname'], 0777  , true);

            return rename($sourse, $distination);

        }
        /* добавить файл */
        public static function add($params, $checkExifDate = false, $date = null){

            if (empty($params['type_id']))
                $params['type_id'] = Files::TYPE_PHOTO;

            if (empty($params['path']))
                return false;

            $type = $params['type_id'];
            $path = $params['path'];

            try {
                if (!$info = getimagesize(UPLOAD_PATH . '/' . $path))
                    return false;

                // превьюхи отсекаем
                if ($info[0] < 350 && $info[1] < 350)
                    return false;

            } catch (\Exception $e) {
                Log::add([
                    'message' => 'Size error: '.$path.'; '.$e->getMessage(),
                    'context' => 'Files::add'
                ]);
                static::renameFile(UPLOAD_PATH . '/' . $path, UPLOAD_PATH . '/errorFiles/' . $path);

                return false;
            }


            // ищем дубли
            $f0 = Files::findbyPath($path);
            if (!empty ($f0)) return false;

            $f = new Files();
            $f->setAttributes($params);

            $exif = [];
            if ($type == Files::TYPE_PHOTO && $checkExifDate){
                try {
                    $exif = exif_read_data(UPLOAD_PATH . '/' . $path);
                } catch (\Exception $e) {
                    Log::add([
                        'message' => 'Exif error: '.$path.'; '.$e->getMessage(),
                        'context' => 'Files::add'
                    ]);
                    static::renameFile(UPLOAD_PATH . '/' . $path, UPLOAD_PATH . '/errorFiles/' . $path);

                    return false;
                }

                if (empty($exif['DateTimeOriginal']))
                    return false;
            }

            if (!is_null($date))
                $f->date_id = $date;
            elseif (!empty($exif['DateTimeOriginal'])){
                $f->date_id = $exif['DateTimeOriginal'];
            } else
                $f->date_id = date('Y-m-d H:i:s', filemtime (UPLOAD_PATH . '/' . $path));

            return $f->save();
        }

        /* импорт файлов */
        public static function importFilesFromFolder($path, $type_id = Files::TYPE_PHOTO, $checkExifData = true, $dateInFilenameFormat ='', $fileParams = []){

            if ($handle = opendir(UPLOAD_PATH.'/'.$path)) {
                while ($entry = readdir($handle)) {

                    if (is_dir(UPLOAD_PATH.'/'.$path.'/'.$entry)){
                        if (!in_array($entry, ['.','..'])){
                            if (file_exists(UPLOAD_PATH.'/'.$path.'/'.$entry.'/params.txt')){
                                $fileParams = Json::decode(file_get_contents(UPLOAD_PATH.'/'.$path.'/'.$entry.'/params.txt'));
                            }
                            static::importFilesFromFolder($path.'/'.$entry, $type_id, $checkExifData, $dateInFilenameFormat, $fileParams);
                        }
                    } elseif ($entry != 'params.txt') {
                        $date = null;
                        if (!$checkExifData && !empty($dateInFilenameFormat)){
                            preg_match_all($dateInFilenameFormat['pattern'],$entry, $matches);
                            $d = $dateInFilenameFormat['date'];
                            $date = $matches[$d['y']][0].'-'.$matches[$d['m']][0].'-'.$matches[$d['d']][0].' '
                                .$matches[$d['h']][0].':'.$matches[$d['i']][0].':'.$matches[$d['s']][0];
                        }

                        Files::add($fileParams + [
                            'path' => $path.'/'.$entry,
                            'type_id' => $type_id
                        ], $checkExifData, $date);
                    }
                }
                closedir($handle);
            }

        }

        /* проверка существования файлов и удаление из базы */
        public static function removeNonExistFiles(){
            $files = static::find()->all();

            foreach ($files as $file){
                $filePath = UPLOAD_PATH . '/' . $file->path;
                if (!file_exists($filePath)){
                    Log::add([
                        'message' => 'Not exist: '.$file->path,
                        'context' => 'checkFilesExist'
                    ]);
                    $file->delete();
                }
            }

        }

        public static function getItemsForDay($date, $ignoreEvent = true){
            $blog = Yii::$app->cache->getOrSet('files-for-date-'.$date, function() use ($date, $ignoreEvent) {
                $query = Files::find()->where('DATE(`date_id`) = :date' , [':date' => $date])->orderBy('date_id');
                if ($ignoreEvent)
                    $query->andWhere("`event_id` = 0");

                return $query->all();
            } ,3600*24, Blog::getCacheDependency());

            return $blog;
        }

        public static function getItemsForEvent($id){
            $blog = Yii::$app->cache->getOrSet('files-for-event-'.$id, function() use ($id) {
                $query = Files::find()->where('`event_id` = :event_id' , [':event_id' => $id])->orderBy('date_id');
                return $query->all();
            } ,3600*24, Blog::getCacheDependency());

            return $blog;
        }

        // возращает имя превьюшки файла
        public static function thumb($path, $width = false, $height = false, $absolutePath = false, $rewriteFile = false)
        {
            if (!$path)
                return false;

            if (!file_exists($path))
            {
                $path = UPLOAD_PATH.$path;

                if (!file_exists($path))
                    return false;
            }


            $time = filectime($path);
            $info = pathinfo ($path);

            $info['tname'] = '';
            $info['tname'] .= $width?'w'.$width:'';
            $info['tname'] .= $height?'h'.$height:'';

            $filename = $info['dirname'].'/'.$info['filename'].$info['tname'].'.'.$info['extension'];

            $alt_filename = '';

            //превьюшки JPG-файлов могут иметь расширение .jpeg - этот вариант надо проверять
            if (strtolower($info['extension']) == 'jpg') {
                $alt_filename = $info['dirname'] . '/' . $info['filename'] . $info['tname'] . '.jpeg';
            }

            if ($info['tname'])
            {
                if (!empty($alt_filename) && !$rewriteFile && file_exists($alt_filename))
                    $filename = $alt_filename;
                else if (!file_exists($filename) || $rewriteFile)
                {
                    // не работаем с файлами более 20Мб в этот раз
                    if (filesize($path) > (1024*1024)*20) return false;

                    $image = new ImageResizer( $path );
                    //если формат изображения не соответствует его расширению, то меняем имя превьюшки
                    if (strtolower($info['extension']) != strtolower($image->getFormat()))
                    {
                        $filename = $info['dirname'].'/'.$info['filename'].$info['tname'].'.'.$image->getFormat();
                    }

                    if (!file_exists($filename) || $rewriteFile) {
                        if ($width && $height) {
                            $image->resize($filename, $width, $height);
                        } elseif ($width) {
                            $image->resizeW($filename, $width);
                        } elseif
                        ($height
                        ) {
                            $image->resizeH($filename, $height);
                        }
                    }
                }
            }

            if (!$absolutePath)
            {
                $filename = str_replace(UPLOAD_PATH, UPLOAD_WWW, $filename);
                $filename = str_replace('/', '/', $filename);
                $filename .= '?r='.$time;
            }

            return $filename;
        }
    }