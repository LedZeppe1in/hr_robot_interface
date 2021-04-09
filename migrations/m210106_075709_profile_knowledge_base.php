<?php

use yii\db\Migration;

/**
 * Class m210106_075709_profile_knowledge_base
 */
class m210106_075709_profile_knowledge_base extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%profile_knowledge_base}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'profile_id' => $this->integer(),
            'first_level_knowledge_base_id' => $this->integer(),
            'second_level_knowledge_base_id' => $this->integer(),
        ], $tableOptions);

        $this->addForeignKey("profile_knowledge_base_profile_fk", "{{%profile_knowledge_base}}",
            "profile_id", "{{%profile}}", "id", 'CASCADE');
        $this->addForeignKey("profile_knowledge_base_first_level_knowledge_base_fk",
            "{{%profile_knowledge_base}}", "first_level_knowledge_base_id",
            "{{%knowledge_base}}", "id", 'CASCADE');
        $this->addForeignKey("profile_knowledge_base_second_level_knowledge_base_fk",
            "{{%profile_knowledge_base}}", "second_level_knowledge_base_id",
            "{{%knowledge_base}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%profile_knowledge_base}}');
    }
}