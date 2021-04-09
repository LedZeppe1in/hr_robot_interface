<?php

use yii\db\Migration;

/**
 * Class m200630_120545_final_result
 */
class m200630_120545_final_result extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%final_result}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'description' => $this->text(),
            'video_interview_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("final_result_video_interview_fk", "{{%final_result}}",
            "video_interview_id", "{{%video_interview}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%final_result}}');
    }
}