<?php

use yii\db\Migration;

/**
 * Class m250101_000000_add_quest_summary_access_right
 */
class m250101_000000_add_quest_summary_access_right extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('{{%access_right}}', [
            'id' => 1209,
            'route' => 'quest',
            'action' => 'summary',
            'application' => 'app-frontend',
            'is_admin' => 0,
            'is_designer' => 0,
            'is_player' => 1,
            'has_player' => 1,
            'in_quest' => 1,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%access_right}}', ['id' => 1209]);
    }
}
