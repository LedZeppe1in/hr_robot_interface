<?php

use yii\db\Migration;

/**
 * Class m200619_080231_answer_relation_to_evaluated_answer
 */
class m200619_080231_answer_relation_to_evaluated_answer extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%answer_relation_to_evaluated_answer}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'evaluated_answer_id' => $this->integer()->notNull(),
            'answer_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_answer_relation_to_evaluated_answer',
            '{{%answer_relation_to_evaluated_answer}}',
            ['evaluated_answer_id', 'answer_id'], true);

        $this->addForeignKey("answer_relation_to_evaluated_answer_evaluated_answer_fk",
            "{{%answer_relation_to_evaluated_answer}}", "evaluated_answer_id",
            "{{%evaluated_answer}}", "id", 'CASCADE');
        $this->addForeignKey("answer_relation_to_evaluated_answer_answer_fk",
            "{{%answer_relation_to_evaluated_answer}}", "answer_id",
            "{{%answer}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%answer_relation_to_evaluated_answer}}');
    }
}