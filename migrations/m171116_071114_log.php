<?php

use yii\db\Migration;

/**
 * Class m171116_071114_log
 */
class m171116_071114_log extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->execute('
        CREATE TABLE IF NOT EXISTS {{%log}} (
            `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `date_id` datetime NOT NULL,
            `message` text NOT NULL,
            `context` text NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

        ');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%log}}');
        ;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171116_071114_log cannot be reverted.\n";

        return false;
    }
    */
}
