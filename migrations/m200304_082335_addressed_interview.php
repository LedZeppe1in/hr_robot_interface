<?php

use yii\db\Migration;

/**
 * Class m200304_082335_addressed_interview
 */
class m200304_082335_addressed_interview extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%addressed_interview}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'video_interview_id' => $this->integer()->notNull(),
            'customer_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("addressed_interview_video_interview_fk",
            "{{%addressed_interview}}", "video_interview_id",
            "{{%video_interview}}", "id", 'CASCADE');
        $this->addForeignKey("addressed_interview_customer_fk",
            "{{%addressed_interview}}", "customer_id",
            "{{%customer}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%addressed_interview}}');
    }
}