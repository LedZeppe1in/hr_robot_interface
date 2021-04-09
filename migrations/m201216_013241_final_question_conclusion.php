<?php

use yii\db\Migration;

/**
 * Class m201216_013241_final_question_conclusion
 */
class m201216_013241_final_question_conclusion extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%final_question_conclusion}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'conclusion' => $this->text(),
            'final_conclusion_id' => $this->integer()->notNull(),
            'question_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("final_question_conclusion_final_conclusion_fk",
            "{{%final_question_conclusion}}", "final_conclusion_id",
            "{{%final_conclusion}}", "id", 'CASCADE');
        $this->addForeignKey("final_question_conclusion_question_fk", "{{%final_question_conclusion}}",
            "question_id", "{{%question}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%final_question_conclusion}}');
    }
}