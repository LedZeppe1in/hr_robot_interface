<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%landmark}}`.
 */
class m200520_120207_add_columns_to_landmark_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%landmark}}', 'rotation', $this->integer());
        $this->addColumn('{{%landmark}}', 'mirroring', $this->boolean());
        $this->addColumn('{{%landmark}}', 'start_time', $this->integer());
        $this->addColumn('{{%landmark}}', 'finish_time', $this->integer());
        $this->addColumn('{{%landmark}}', 'question_id', $this->integer());
        $this->addForeignKey("landmark_question_fk", "{{%landmark}}", "question_id",
            "{{%question}}", "id", 'CASCADE');
    }

    public function down()
    {
        $this->dropColumn('{{%landmark}}', 'rotation');
        $this->dropColumn('{{%landmark}}', 'mirroring');
        $this->dropColumn('{{%landmark}}', 'start_time');
        $this->dropColumn('{{%landmark}}', 'finish_time');
        $this->dropColumn('{{%landmark}}', 'question_id');
        $this->dropForeignKey('landmark_question_fk', '{{%landmark}}');
    }
}
