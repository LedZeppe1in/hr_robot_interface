<?php

use yii\db\Migration;

/**
 * Class m201118_142034_video_interview_processing_status
 */
class m201118_142034_video_interview_processing_status extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%video_interview_processing_status}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'all_runtime' => $this->integer(),
            'emotion_interpretation_runtime' => $this->integer(),
            'video_interview_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("video_interview_processing_status_video_interview_fk",
            "{{%video_interview_processing_status}}", "video_interview_id",
            "{{%video_interview}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%video_interview_processing_status}}');
    }
}