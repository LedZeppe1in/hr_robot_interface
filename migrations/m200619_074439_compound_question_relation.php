<?php

use yii\db\Migration;

/**
 * Class m200619_074439_compound_question_relation
 */
class m200619_074439_compound_question_relation extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%compound_question_relation}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'parent_test_question_id' => $this->integer()->notNull(),
            'child_test_question_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("compound_question_relation_test_question_fk",
            "{{%compound_question_relation}}", "parent_test_question_id",
            "{{%test_question}}", "id", 'CASCADE');
        $this->addForeignKey("simple_question_relation_test_question_fk",
            "{{%compound_question_relation}}", "child_test_question_id",
            "{{%test_question}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%compound_question_relation}}');
    }
}