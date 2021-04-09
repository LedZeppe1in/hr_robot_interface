<?php

use yii\db\Migration;

/**
 * Class m200619_075840_evaluated_answer
 */
class m200619_075840_evaluated_answer extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%evaluated_answer}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'text' => $this->text(),
            'description' => $this->text(),
            'evaluated_question_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("evaluated_answer_evaluated_question_fk", "{{%evaluated_answer}}",
            "evaluated_question_id", "{{%evaluated_question}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%evaluated_answer}}');
    }
}