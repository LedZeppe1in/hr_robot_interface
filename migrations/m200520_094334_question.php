<?php

use yii\db\Migration;

/**
 * Class m200520_094334_question
 */
class m200520_094334_question extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%question}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'text' => $this->text()->notNull(),
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%question}}');
    }
}