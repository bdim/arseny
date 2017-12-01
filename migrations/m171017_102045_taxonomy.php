<?php

use yii\db\Migration;

class m171017_102045_taxonomy extends Migration
{
    public function safeUp()
    {
        $this->execute('
        CREATE TABLE IF NOT EXISTS {{%taxonomy_map}} (
            `model_id` int(10) UNSIGNED NOT NULL,
            `model_name` enum("Blog","Files","Event","Article") NOT NULL DEFAULT "Blog",
            `tid` int(10) UNSIGNED NOT NULL,
              PRIMARY KEY `tid_blog` (`tid`,`model_id`, `model_name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

        ALTER TABLE {%taxonomy_map}} ADD KEY `model_name` (`model_name`)
        ');
    }

    public function safeDown()
    {
        $this->dropTable('{{%taxonomy_map}}');

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171017_102045_taxonomy cannot be reverted.\n";

        return false;
    }
    */
}
