<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%question}}`.
 */
class m200623_023102_add_columns_to_question_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%question}}', 'video_file_name', $this->string());
        $this->addColumn('{{%question}}', 'description', $this->text());
        $this->addColumn('{{%question}}', 'test_question_id', $this->integer());
        $this->addColumn('{{%question}}', 'video_interview_id', $this->integer());

        $this->addForeignKey("test_question_video_interview_question_fk", "{{%question}}",
            "test_question_id", "{{%test_question}}", "id", 'CASCADE');
        $this->addForeignKey("video_interview_question_fk", "{{%question}}",
            "video_interview_id", "{{%video_interview}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropColumn('{{%question}}', 'text');
        $this->dropColumn('{{%question}}', 'video_file_name');
        $this->dropColumn('{{%question}}', 'description');

        $this->dropForeignKey('test_question_video_interview_question_fk', '{{%question}}');
        $this->dropForeignKey('video_interview_question_fk', '{{%question}}');
    }
}