<?php

use yii\db\Migration;

/**
 * Class m200330_064343_advanced_landmark
 */
class m200330_064343_advanced_landmark extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%advanced_landmark}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'file_name' => $this->string(),
            'video_interview_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("advanced_landmark_video_interview_fk",
            "{{%advanced_landmark}}", "video_interview_id",
            "{{%video_interview}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%advanced_landmark}}');
    }
}