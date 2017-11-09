<?php

use yii\db\Migration;

class m171107_110050_events extends Migration
{
    public function safeUp()
    {
        $this->execute('
        CREATE TABLE IF NOT EXISTS {{%event}} (
            `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `event_date` date NOT NULL,
            `child_id` VARCHAR(255) NOT NULL DEFAULT 0,
            `user_id`  VARCHAR(255) NOT NULL DEFAULT 0,
            `title` text NOT NULL,
            `post_text` text NOT NULL,

              PRIMARY KEY (`id`),
              KEY `event_date` (`event_date`)
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
