<?php

use yii\db\Migration;

/**
 * Class m200619_075354_answer_relation_to_answer_template
 */
class m200619_075354_answer_relation_to_answer_template extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%answer_relation_to_answer_template}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'answer_template_id' => $this->integer()->notNull(),
            'answer_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx_answer_relation_to_answer_template',
            '{{%answer_relation_to_answer_template}}',
            ['answer_template_id', 'answer_id'], true);

        $this->addForeignKey("answer_relation_to_answer_template_answer_template_fk",
            "{{%answer_relation_to_answer_template}}", "answer_template_id",
            "{{%answer_template}}", "id", 'CASCADE');
        $this->addForeignKey("answer_relation_to_answer_template_answer_fk",
            "{{%answer_relation_to_answer_template}}", "answer_id",
            "{{%answer}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%answer_relation_to_answer_template}}');
    }
}