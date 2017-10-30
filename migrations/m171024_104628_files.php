<?php

use yii\db\Migration;

class m171024_104628_files extends Migration
{
    public function safeUp()
    {
        $this->execute('
        CREATE TABLE IF NOT EXISTS {{%files}} (
            `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `type_id` tinyint(2) UNSIGNED NOT NULL,
            `path` VARCHAR(255) NOT NULL,
            `caption` VARCHAR(255) NOT NULL,
            `date_id` datetime NOT NULL,
             PRIMARY KEY (`id`),
             KEY `type_id` (`type_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;

        ALTER TABLE {{%files}} ADD UNIQUE(`path`);

        ');
    }

    public function safeDown()
    {
        $this->dropTable('{{%files}}');

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171024_104628_files cannot be reverted.\n";

        return false;
    }
    */
}
