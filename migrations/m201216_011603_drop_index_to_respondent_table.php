<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%index_to_respondent}}`.
 */
class m201216_011603_drop_index_to_respondent_table extends Migration
{
    public function up()
    {
        $this->dropIndex('idx_respondent_name', '{{%respondent}}');
    }
}