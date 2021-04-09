<?php

use yii\db\Migration;

/**
 * Class m200619_075156_answer
 */
class m200619_075156_answer extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%answer}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'type' => $this->smallInteger()->notNull()->defaultValue(0),
            'text' => $this->text(),
            'description' => $this->text(),
            'index' => $this->integer()->notNull()->defaultValue(0),
            'test_question_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("answer_test_question_fk", "{{%answer}}", "test_question_id",
            "{{%test_question}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%answer}}');
    }
}