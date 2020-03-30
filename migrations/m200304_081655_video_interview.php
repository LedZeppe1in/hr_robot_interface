<?php

use yii\db\Migration;

/**
 * Class m200304_081655_video_interview
 */
class m200304_081655_video_interview extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%video_interview}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'video_file_name' => $this->string(),
            'landmark_file_name' => $this->string(),
            'respondent_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("video_interview_respondent_fk",
            "{{%video_interview}}", "respondent_id",
            "{{%respondent}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%video_interview}}');
    }
}