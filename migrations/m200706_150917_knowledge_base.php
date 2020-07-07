<?php

use yii\db\Migration;

/**
 * Class m200706_150917_knowledge_base
 */
class m200706_150917_knowledge_base extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql')
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

        $this->createTable('{{%knowledge_base}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'knowledge_base_file_name' => $this->string(),
            'description' => $this->text(),
        ], $tableOptions);

        $this->createIndex('idx_knowledge_base_name', '{{%knowledge_base}}', 'name');
    }

    public function down()
    {
        $this->dropTable('{{%knowledge_base}}');
    }
}