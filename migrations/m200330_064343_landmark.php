<?php

use yii\db\Migration;

/**
 * Class m200330_064343_landmark
 */
class m200330_064343_landmark extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%landmark}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'landmark_file_name' => $this->string(),
            'description' => $this->text(),
            'video_interview_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("landmark_video_interview_fk", "{{%landmark}}", "video_interview_id",
            "{{%video_interview}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%landmark}}');
    }
}