<?php

use yii\db\Migration;

class m171107_110050_events extends Migration
{
    public function safeUp()
    {
        $this->execute('
        CREATE TABLE IF NOT EXISTS {{%event}} (
            `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `date_start` date NOT NULL,
            `date_end` date NOT NULL,
            `publish_date` datetime NOT NULL,
            `user_id`  VARCHAR(255) NOT NULL DEFAULT 0,
            `title` text NOT NULL,
            `body` text NOT NULL,
            `post_text` text NOT NULL,

              PRIMARY KEY (`id`),
              KEY `publish_date` (`publish_date`),
              KEY `date_start` (`date_start`),
              KEY `date_end` (`date_end`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

        ');
    }

    public function safeDown()
    {
        $this->dropTable('{{%event}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171107_110050_events cannot be reverted.\n";

        return false;
    }
    */
}
