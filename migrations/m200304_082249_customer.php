<?php

use yii\db\Migration;

/**
 * Class m200304_082249_customer
 */
class m200304_082249_customer extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%customer}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_customer_name', '{{%customer}}', 'name');
    }

    public function down()
    {
        $this->dropTable('{{%customer}}');
    }
}