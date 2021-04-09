<?php

use yii\db\Migration;

/**
 * Class m200334_082115_analysis_result
 */
class m200334_082115_analysis_result extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%analysis_result}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'detection_result_file_name' => $this->string(),
            'facts_file_name' => $this->string(),
            'interpretation_result_file_name' => $this->string(),
            'description' => $this->text(),
            'landmark_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("analysis_result_landmark_fk", "{{%analysis_result}}", "landmark_id",
            "{{%landmark}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%analysis_result}}');
    }
}