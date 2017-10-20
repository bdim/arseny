<?php

use yii\db\Migration;

class m171019_095809_user_chatid extends Migration
{
    public function safeUp()
    {
        $this->execute('ALTER TABLE {{%user}} ADD COLUMN `telegram_id` VARCHAR(255), ADD INDEX telegram_id (`telegram_id`)  ;

        ');
    }

    public function safeDown()
    {
        $this->execute('ALTER TABLE {{%user}} DROP COLUMN `telegram_id`');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171019_095809_user_chatid cannot be reverted.\n";

        return false;
    }
    */
}
