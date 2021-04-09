<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%test_question}}`.
 */
class m201110_101515_add_columns_to_test_question_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%test_question}}', 'time', $this->integer());
        $this->addColumn('{{%test_question}}', 'audio_file_name', $this->string());
    }

    public function down()
    {
        $this->dropColumn('{{%test_question}}', 'time');
        $this->dropColumn('{{%test_question}}', 'audio_file_name');
    }
}