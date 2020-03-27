<?php

use yii\db\Migration;

/**
 * Class m200304_082115_analysis_result
 */
class m200304_082115_analysis_result extends Migration
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
            'interpretation_result_file_name' => $this->string(),
            'video_interview_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("analysis_result_video_interview_fk",
            "{{%analysis_result}}", "video_interview_id",
            "{{%video_interview}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%analysis_result}}');
    }
}