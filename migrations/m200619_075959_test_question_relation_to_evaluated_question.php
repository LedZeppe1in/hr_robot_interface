<?php

use yii\db\Migration;

/**
 * Class m200619_075959_test_question_relation_to_evaluated_question
 */
class m200619_075959_test_question_relation_to_evaluated_question extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%test_question_relation_to_evaluated_question}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'evaluated_question_id' => $this->integer()->notNull(),
            'test_question_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("test_question_relation_to_evaluated_evaluated_question_fk",
            "{{%test_question_relation_to_evaluated_question}}", "evaluated_question_id",
            "{{%evaluated_question}}", "id", 'CASCADE');
        $this->addForeignKey("test_question_relation_to_evaluated_question_test_question_fk",
            "{{%test_question_relation_to_evaluated_question}}", "test_question_id",
            "{{%test_question}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%test_question_relation_to_evaluated_question}}');
    }
}