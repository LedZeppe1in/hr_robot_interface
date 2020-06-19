<?php

use yii\db\Migration;

/**
 * Class m200619_075709_evaluated_question
 */
class m200619_075709_evaluated_question extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%evaluated_question}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'description' => $this->text(),
            'evaluation_method_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("evaluated_question_evaluation_method_fk", "{{%evaluated_question}}",
            "evaluation_method_id", "{{%evaluation_method}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%evaluated_question}}');
    }
}