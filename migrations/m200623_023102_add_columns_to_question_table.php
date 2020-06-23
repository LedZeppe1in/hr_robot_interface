<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%question}}`.
 */
class m200623_023102_add_columns_to_question_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%question}}', 'type', $this->smallInteger()->defaultValue(0));
        $this->addColumn('{{%question}}', 'time', $this->integer());
        $this->addColumn('{{%question}}', 'audio_file_name', $this->string());
        $this->addColumn('{{%question}}', 'description', $this->text());
    }

    public function down()
    {
        $this->dropColumn('{{%question}}', 'type');
        $this->dropColumn('{{%question}}', 'time');
        $this->dropColumn('{{%question}}', 'audio_file_name');
        $this->dropColumn('{{%question}}', 'description');
    }
}