<?php

use yii\db\Migration;

/**
 * Class m200619_074932_topic_question
 */
class m200619_074932_topic_question extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%topic_question}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'topic_id' => $this->integer()->notNull(),
            'test_question_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("topic_question_topic_fk", "{{%topic_question}}", "topic_id",
            "{{%topic}}", "id", 'CASCADE');
        $this->addForeignKey("topic_question_test_question_fk", "{{%topic_question}}",
            "test_question_id", "{{%test_question}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%topic_question}}');
    }
}