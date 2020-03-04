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
            'name' => $this->string()->notNull(),
            'video_interview_id' => $this->integer()->notNull(),
            'feature_detection_result' => $this->text(),
            'feature_interpretation_result' => $this->text(),
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