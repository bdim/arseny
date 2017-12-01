<?
namespace app\components;

use Yii;
use yii\db\ActiveRecord;
use yii\base\Behavior;
use app\models\Taxonomy;
use app\models\TaxonomyMap;
use yii\helpers\VarDumper;

class TaxonomyBehavior extends Behavior
{
    public $_tag; // Это про ког пишем, есть еще keywords - они отдельно
    public $_tagsIds = null;
    public $_tagsNames = null;

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
        ];
    }

    /* relation Taxonomy-map */
    public function getTaxonomy()
    {
        $className = end(explode("\\", $this->owner->className()));
        $q =  $this->owner->hasMany(TaxonomyMap::className(), ['model_id' => 'id'])->andOnCondition("`model_name` = :model_name", [':model_name' => $className]);
        return $q;
    }

    public function afterSave($event)
    {
        if (!empty($this->tag)){ // про кого пишем

            $className = end(explode("\\", $this->owner->className()));

            // удаляем все теги словаря VID_BLOG_TAG
            Yii::$app->db->createCommand('DELETE m.* FROM {{%taxonomy_map}} m LEFT JOIN {{%taxonomy_data}} t ON m.`tid` = t.`tid`
                                                  WHERE m.`model_id` = :model_id AND m.`model_name` = :model_name AND t.`vid` = :vid;',
                [
                    ':model_id' => $this->owner->id,
                    ':model_name' => $className,
                    ':vid'     => Taxonomy::VID_BLOG_TAG,
                ]
            )->execute();

            if (!is_array($this->tag))
                $this->tag = [$this->tag];

            foreach ($this->tag as $tag){
                if (is_numeric($tag))
                    $tagId = $tag;
                else
                    $tagId = Taxonomy::getIdByName($tag, Taxonomy::VID_BLOG_TAG);

                Yii::$app->db->createCommand('INSERT IGNORE into {{%taxonomy_map}} (`model_id`, `model_name`, `tid` ) VALUES (:model_id, :model_name, :tid) ',
                    [
                        ':model_id' => $this->owner->id,
                        ':model_name' => $className,
                        ':tid'     => $tagId,
                    ]
                )->execute();
            }
        }
    }

    public function getTagsIds(){

        if (is_null($this->_tagsIds[$this->owner->className()])){
            $this->_tagsIds = [];
            foreach ($this->owner->taxonomy as $tax){
                $this->_tagsIds[] =$tax->tid;
            }
        }

        return $this->_tagsIds;
    }

    public function getTag(){
        return $this->getTagsIds();
    }
    public function setTag($tag){
        $this->tag = $tag;
    }

    public function getTagNames(){

        if (is_null($this->_tagsNames)){
            $this->_tagsNames = [];
            if (!empty($this->tagsIds))
                foreach ($this->tagsIds as $id)
                    $this->_tagsNames[$id] = Taxonomy::getNameById($id);
        }

        return $this->_tagsNames;
    }


    public function addKeywords($keywords){
        if (empty($keywords)) return;

        if (!is_array($keywords))
            $keywords = [$keywords];

        $className = end(explode("\\", $this->owner->className()));

        foreach ($keywords as $name)
            Yii::$app->db->createCommand('INSERT IGNORE into {{%taxonomy_map}} (`model_id`, `model_name`, `tid` ) VALUES (:model_id, :model_name, :tid) ',
                [
                    ':model_id' => $this->owner->id,
                    ':model_name' => $className,
                    ':tid'     => is_numeric($name) ? $name : Taxonomy::getIdByName($name)
                ]
            )->execute();
    }

}