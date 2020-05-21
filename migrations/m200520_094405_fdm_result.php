<?php

use yii\db\Migration;

/**
 * Class m200520_094405_fdm_result
 */
class m200520_094405_fdm_result extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%fdm_result}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'result' => $this->text()->notNull(),
            'description' => $this->text(),
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%fdm_result}}');
    }
}