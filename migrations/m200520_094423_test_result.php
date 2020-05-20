<?php

use yii\db\Migration;

/**
 * Class m200520_094423_test_result
 */
class m200520_094423_test_result extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%test_result}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'description' => $this->text(),
            'experiment_specification_id' => $this->integer()->notNull(),
            'fdm_result_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("test_result_experiment_specification_fk", "{{%test_result}}",
            "experiment_specification_id", "{{%experiment_specification}}",
            "id", 'CASCADE');
        $this->addForeignKey("test_result_fdm_result_fk", "{{%test_result}}",
            "fdm_result_id", "{{%fdm_result}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%test_result}}');
    }
}