<?php

use yii\db\Migration;

/**
 * Class m201118_142104_question_processing_status
 */
class m201118_142104_question_processing_status extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%question_processing_status}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'ivan_video_analysis_runtime' => $this->integer(),
            'andrey_video_analysis_runtime' => $this->integer(),
            'feature_detection_runtime' => $this->integer(),
            'feature_interpretation_runtime' => $this->integer(),
            'question_id' => $this->integer()->notNull(),
            'video_interview_processing_status_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("question_processing_status_question_fk", "{{%question_processing_status}}",
            "question_id", "{{%question}}", "id", 'CASCADE');
        $this->addForeignKey("question_processing_status_video_interview_processing_status_fk",
            "{{%question_processing_status}}", "video_interview_processing_status_id",
            "{{%video_interview_processing_status}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%question_processing_status}}');
    }
}