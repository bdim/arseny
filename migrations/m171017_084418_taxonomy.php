<?php

use yii\db\Migration;

class m171017_084418_taxonomy extends Migration
{
    public function safeUp()
    {
        $this->execute('
        CREATE TABLE IF NOT EXISTS {{%taxonomy_data}} (
  `tid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vid` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT "",
  `description` longtext  ,
  `format` varchar(255) DEFAULT NULL,
  `weight` int(11) NOT NULL DEFAULT 0,
  `uuid` char(36) NOT NULL DEFAULT "",
  PRIMARY KEY (`tid`),
  KEY `taxonomy_tree` (`vid`,`weight`,`name`),
  KEY `vid_name` (`vid`,`name`),
  KEY `name` (`name`),
  KEY `uuid` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 ;

        ');
    }

    public function safeDown()
    {
        $this->dropTable('{{%taxonomy_data}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171017_084418_taxonomy cannot be reverted.\n";

        return false;
    }
    */
}
