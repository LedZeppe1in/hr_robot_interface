<?php

use yii\db\Migration;

/**
 * Class m210208_150827_calibration_conclusion
 */
class m210208_150827_calibration_conclusion extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%calibration_conclusion}}', [
            'id' => $this->integer()->notNull(),
            'text' => $this->text()->notNull(),
        ], $tableOptions);

        $this->addPrimaryKey("calibration_conclusion_pkey", "{{%calibration_conclusion}}", "id");

        $this->addForeignKey("calibration_conclusion_final_result_fk", "{{%calibration_conclusion}}",
            "id", "{{%final_result}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%calibration_conclusion}}');
    }
}