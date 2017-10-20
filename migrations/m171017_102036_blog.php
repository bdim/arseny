<?php

use yii\db\Migration;

class m171017_102036_blog extends Migration
{
    public function safeUp()
    {
        $this->execute('
        CREATE TABLE IF NOT EXISTS {{%blog}} (
            `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            `publish_date` datetime NOT NULL,
            `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
            `title` varchar(255) NOT NULL,
            `body` text NOT NULL,
            `photo` varchar(255) NOT NULL,

              PRIMARY KEY (`id`),
              KEY `publish_date` (`publish_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

        ');
    }

    public function safeDown()
    {
        $this->dropTable('{{%blog}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171017_102036_blog cannot be reverted.\n";

        return false;
    }
    */
}
