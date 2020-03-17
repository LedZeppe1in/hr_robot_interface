<?php

use yii\db\Migration;

/**
 * Class m200304_075955_respondent
 */
class m200304_075955_respondent extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%respondent}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_respondent_name', '{{%respondent}}', 'name');
    }

    public function down()
    {
        $this->dropTable('{{%respondent}}');
    }
}