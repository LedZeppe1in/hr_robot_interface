<?php

use yii\db\Migration;

/**
 * Class m200619_073533_profile_survey
 */
class m200619_073533_profile_survey extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%profile_survey}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'survey_id' => $this->integer()->notNull(),
            'profile_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey("profile_survey_survey_fk", "{{%profile_survey}}", "survey_id",
            "{{%survey}}", "id", 'CASCADE');
        $this->addForeignKey("profile_survey_profile_fk", "{{%profile_survey}}", "profile_id",
            "{{%profile}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%profile_survey}}');
    }
}