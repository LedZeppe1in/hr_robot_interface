<?php

use yii\db\Migration;

/**
 * Class m200701_103910_final_conclusion
 */
class m200701_103910_final_conclusion extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%final_conclusion}}', [
            'id' => $this->integer()->notNull(),
            'conclusion' => $this->text(),
        ], $tableOptions);

        $this->addPrimaryKey("final_conclusion_pkey", "{{%final_conclusion}}", "id");

        $this->addForeignKey("final_conclusion_final_result_fk", "{{%final_conclusion}}",
            "id", "{{%final_result}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%final_conclusion}}');
    }
}