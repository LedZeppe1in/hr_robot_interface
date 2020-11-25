<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%landmark}}`.
 */
class m201125_123337_add_processed_video_file_name_column_to_landmark_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%landmark}}', 'processed_video_file_name', $this->string());
    }

    public function down()
    {
        $this->dropColumn('{{%landmark}}', 'type');
    }
}