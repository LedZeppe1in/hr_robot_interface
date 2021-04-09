<?php

use yii\db\Migration;

/**
 * Class m200608_032247_test_result_statistics
 */
class m200608_032247_test_result_statistics extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%test_result_statistics}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'statistics_value' => $this->text(),
            'statistics_parameter' => $this->text(),
            'description' => $this->text(),
            'test_result_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("test_result_statistics_test_result_fk", "{{%test_result_statistics}}",
            "test_result_id", "{{%test_result}}",
            "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%test_result_statistics}}');
    }
}