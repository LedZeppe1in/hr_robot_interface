<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%respondent}}`.
 */
class m201216_012916_add_columns_to_respondent_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%respondent}}', 'main_respondent_id', $this->integer());

        $this->addForeignKey("respondent_main_respondent_fk", "{{%respondent}}",
            "main_respondent_id", "{{%main_respondent}}", "id", 'CASCADE');

        $this->createIndex('idx_respondent_name', '{{%respondent}}', 'name', true);
    }

    public function down()
    {
        $this->dropColumn('{{%respondent}}', 'main_respondent_id');

        $this->dropForeignKey('respondent_main_respondent_fk', '{{%respondent}}');

        $this->dropIndex('idx_respondent_name', '{{%respondent}}');
    }
}