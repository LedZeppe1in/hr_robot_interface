<?php

use yii\db\Migration;

/**
 * Class m200619_074712_survey_question
 */
class m200619_074712_survey_question extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%survey_question}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'index' => $this->integer()->notNull(),
            'survey_id' => $this->integer()->notNull(),
            'test_question_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("survey_question_survey_fk", "{{%survey_question}}", "survey_id",
            "{{%survey}}", "id", 'CASCADE');
        $this->addForeignKey("survey_question_test_question_fk", "{{%survey_question}}",
            "test_question_id", "{{%test_question}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%survey_question}}');
    }
}