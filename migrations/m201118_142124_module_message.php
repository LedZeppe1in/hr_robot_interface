<?php

use yii\db\Migration;

/**
 * Class m201118_142124_module_message
 */
class m201118_142124_module_message extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%module_message}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'message' => $this->text()->notNull(),
            'module_name' => $this->smallInteger()->notNull()->defaultValue(0),
            'question_processing_status_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("module_message_question_processing_status_fk",
            "{{%module_message}}", "question_processing_status_id",
            "{{%question_processing_status}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%module_message}}');
    }
}