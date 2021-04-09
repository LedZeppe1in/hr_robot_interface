<?php

use yii\db\Migration;

/**
 * Class m201216_011517_sna_result
 */
class m201216_011517_sna_result extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%sna_result}}', [
            'id' => $this->primaryKey(),
            'description' => $this->text(),
            'file_name' => $this->text()->notNull(),
            'main_respondent_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("sna_result_main_respondent_fk", "{{%sna_result}}",
            "main_respondent_id", "{{%main_respondent}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%sna_result}}');
    }
}