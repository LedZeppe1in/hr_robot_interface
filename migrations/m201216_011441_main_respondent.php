<?php

use yii\db\Migration;

/**
 * Class m201216_011441_main_respondent
 */
class m201216_011441_main_respondent extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%main_respondent}}', [
            'id' => $this->primaryKey(),
            'code' => $this->text()->notNull(),
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%main_respondent}}');
    }
}