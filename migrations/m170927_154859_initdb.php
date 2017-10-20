<?php

use yii\db\Migration;

class m170927_154859_initdb extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
 
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
 
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull()->unique(),
            'fio' => $this->string(),
            'info' => $this->text()->notNull(),
            'auth_key' => $this->string(32)->notNull(),
            'telegram' => $this->string()->notNull(),
            'password_hash' => $this->string()->notNull(),
            'password_reset_token' => $this->string()->unique(),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'role' => $this->string(32)->notNull(),
        ], $tableOptions);
    }

    public function safeDown()
    {
	 $this->dropTable('{{%user}}');
	}

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170927_154859_initdb cannot be reverted.\n";

        return false;
    }
    */
}
