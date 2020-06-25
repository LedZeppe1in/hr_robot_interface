<?php

use yii\db\Migration;

/**
 * Class m200619_073923_template_answer_variant
 */
class m200619_073923_template_answer_variant extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%template_answer_variant}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'text' => $this->text(),
            'index' => $this->integer()->notNull()->defaultValue(0),
            'answer_template_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("template_answer_variant_survey_fk", "{{%template_answer_variant}}",
            "answer_template_id", "{{%answer_template}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%template_answer_variant}}');
    }
}