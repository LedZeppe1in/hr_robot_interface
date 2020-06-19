<?php

use yii\db\Migration;

/**
 * Class m200619_072929_topic
 */
class m200619_072929_topic extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%topic}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'description' => $this->text(),
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%topic}}');
    }
}