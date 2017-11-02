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
                ['path' , 'unique'],
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

        public static function findbyPath($path)
        {
            return static::findOne(['path' => $path]);
        }

        public static function last($limit = 1){
            return static::find()->orderBy('id DESC')->limit($limit)->all();
        }

        public static function add($path, $type = 1, $caption ='', $date = null){
            $f = new Files();

            $f0 = Files::findbyPath($path);
            if (!empty ($f0)) return false;

            $f->setAttributes([
                'path'    => $path,
                'type_id' => $type,
                'caption' => $caption
            ]);

            $exif = exif_read_data(IMAGES_PATH.'/'.$path);
            if (!is_null($date))
                $f->date_id = $date;
            elseif (!empty($exif['DateTimeOriginal'])){
                $f->date_id = $exif['DateTimeOriginal'];
            } else
                $f->date_id = date('Y-m-d H:i:s');

            return $f->save();
        }

        public static function getItemsForDay($date){
            $blog = Yii::$app->cache->getOrSet('files-for-date-'.$date, function() use ($date) {
                $query = Files::find()->where('DATE(`date_id`) = :date' , [':date' => $date])->all();
                return $query;
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
                $path = IMAGES_PATH.$path;

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
                $filename = str_replace(IMAGES_PATH, IMAGES_WWW, $filename);
                $filename = str_replace('/', '/', $filename);
                $filename .= '?r='.$time;
            }

            return $filename;
        }
    }