<?php

use yii\db\Migration;

class m171017_102045_taxonomy extends Migration
{
    public function safeUp()
    {
        $this->execute('
        CREATE TABLE IF NOT EXISTS {{%taxonomy_map}} (
            `blog_id` int(10) UNSIGNED NOT NULL,
            `tid` int(10) UNSIGNED NOT NULL,
              PRIMARY KEY `tid_blog` (`tid`,`blog_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

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
