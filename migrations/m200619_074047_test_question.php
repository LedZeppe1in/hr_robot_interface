<?php

use yii\db\Migration;

/**
 * Class m200619_074047_test_question
 */
class m200619_074047_test_question extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%test_question}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string(),
            'text' => $this->text()->notNull(),
            'type' => $this->smallInteger()->notNull()->defaultValue(0),
            'maximum_time' => $this->integer()->notNull(),
            'description' => $this->text(),
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%test_question}}');
    }
}