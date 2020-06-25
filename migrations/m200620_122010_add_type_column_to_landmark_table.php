<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%landmark}}`.
 */
class m200620_122010_add_type_column_to_landmark_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%landmark}}', 'type', $this->smallInteger()->defaultValue(0));
    }

    public function down()
    {
        $this->dropColumn('{{%landmark}}', 'type');
    }
}