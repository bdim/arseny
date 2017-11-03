<?php

use yii\db\Migration;

class m171103_111357_article extends Migration
{
    public function safeUp()
    {
        $this->execute('
        CREATE TABLE IF NOT EXISTS {{%article}} (
            `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            `publish_date` datetime NOT NULL,
            `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
            `title` varchar(255) NOT NULL,
            `body` longtext NOT NULL,
            `photo` varchar(255) NOT NULL,

              PRIMARY KEY (`id`),
              KEY `publish_date` (`publish_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

        ');
    }

    public function safeDown()
    {
        $this->dropTable('{{%article}}');

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171103_111357_article cannot be reverted.\n";

        return false;
    }
    */
}
