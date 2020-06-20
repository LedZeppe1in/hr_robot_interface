<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%question}}`.
 */
class m200620_122211_add_time_column_to_question_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%question}}', 'time', $this->integer());
    }

    public function down()
    {
        $this->dropColumn('{{%question}}', 'time');
    }
}