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
    protected $_tag;
    protected $_tagsIds = null;
    protected $_tagsNames = null;

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
        if (!empty($this->_tag)){

            $className = end(explode("\\", $this->owner->className()));

            if (!is_array($this->_tag))
                $this->_tag = [$this->_tag];

            $tagArray = [];
            $vocArray = [];
            foreach ($this->_tag as $tag){
                $str = explode(":", $tag);

                if (!empty($str[1])){
                    $tagArray[] = $str[0];
                    $vocArray[$str[1]]  = $str[1];
                } else {
                    if (is_numeric($tag))
                        $tagId = $tag;
                    else
                        $tagId = Taxonomy::getIdByName($tag, Taxonomy::VID_BLOG_TAG);

                    $tagArray[] = $tagId;
                }
            }

            if (empty($vocArray))
                $vocArray = [Taxonomy::VID_BLOG_TAG];

            foreach ($vocArray as $voc)
                // удаляем все теги словаря
                Yii::$app->db->createCommand('DELETE m.* FROM {{%taxonomy_map}} m LEFT JOIN {{%taxonomy_data}} t ON m.`tid` = t.`tid`
                                                      WHERE m.`model_id` = :model_id AND m.`model_name` = :model_name AND t.`vid` = :vid;',
                    [
                        ':model_id' => $this->owner->id,
                        ':model_name' => $className,
                        ':vid'     => $voc,
                    ]
                )->execute();



            foreach ($tagArray as $tagId){

                if (!empty($tagId))
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

    public function getTag($vocId = null){
        $tags = [];
        foreach ($this->getTagsIds() as $id){
            $tagVoc = Taxonomy::getVocByTagId($id);
            if (empty($vocId) || $vocId == $tagVoc )
                $tags[] = $id. ":" . $tagVoc;
        }

        return $tags;
    }

    public function setTag($tag){
        $this->_tag = $tag;
    }


    public function getTagNames($vocId = 0){
        if (is_null($this->_tagsNames[$vocId])){
            $this->_tagsNames[$vocId] = [];
            if (!empty($this->tagsIds))
                foreach ($this->tagsIds as $id){
                    $tagVoc = Taxonomy::getVocByTagId($id);
                    if (empty($vocId) || $vocId == $tagVoc )
                        $this->_tagsNames[$vocId][$id] = Taxonomy::getNameById($id);
                }
        }

        return $this->_tagsNames[$vocId];
    }


    public function attachTag($keywords){
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