<?
namespace app\widgets;

use app\models\Blog;
use app\models\Files;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\VarDumper;

class MediaWidget extends Widget
{
    public $message;
    public $pub_date;
    public $files;
    public $title;

    public function init()
    {
        parent::init();

        /* медиа */
        if (!empty($this->pub_date)) {
            $this->files = Files::getItemsForDay($this->pub_date, true);

            $blog  = Blog::getItemsForDay($this->pub_date);

            if (!empty($blog))
                $this->title = $blog[0]->title;
        }

    }

    public function run()
    {
        if (!empty($this->files)){
            return $this->render('MediaWidget',['data' => $this->files, 'show_date' => true, 'pub_date' => $this->pub_date, 'title' => $this->title]);
        }
    }
}