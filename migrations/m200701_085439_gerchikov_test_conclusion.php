<?php

use yii\db\Migration;

/**
 * Class m200701_085439_gerchikov_test_conclusion
 */
class m200701_085439_gerchikov_test_conclusion extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%gerchikov_test_conclusion}}', [
            'id' => $this->integer()->notNull(),
            'accept_test' => $this->smallInteger()->defaultValue(0),
            'accept_level' => $this->double(),
            'instrumental_motivation' => $this->integer(),
            'professional_motivation' => $this->integer(),
            'patriot_motivation' => $this->integer(),
            'master_motivation' => $this->integer(),
            'avoid_motivation' => $this->integer(),
            'description' => $this->text(),
        ], $tableOptions);

        $this->addPrimaryKey("gerchikov_test_conclusion_pkey",
            "{{%gerchikov_test_conclusion}}", "id");

        $this->addForeignKey("gerchikov_test_conclusion_final_result_fk", "{{%gerchikov_test_conclusion}}",
            "id", "{{%final_result}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%gerchikov_test_conclusion}}');
    }
}