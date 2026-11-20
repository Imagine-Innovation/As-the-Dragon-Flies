<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Drops the `quest_session` table.
 *
 * The table was the registry of the live WebSocket (Ratchet) connections:
 * session id, socket connection id, player and quest per connection. The
 * Ratchet stack was replaced by the stateless HTTP long polling transport
 * (`notification` table + `EventWaiter`), which no longer reads or writes
 * any quest session: nothing references this table anymore.
 */
class m261119_120000_drop_quest_session_table extends Migration
{
    /**
     * Drops the no longer used WebSocket connection registry.
     */
    public function up(): void
    {
        $this->dropTable('{{%quest_session}}');
    }

    /**
     * Recreates the table as it existed before the drop (same schema as
     * the original dump), for rollback purposes.
     */
    public function down(): void
    {
        $this->createTable('{{%quest_session}}', [
            'id' => $this->string(64)->primaryKey()->comment('Primary key'),
            'quest_id' => $this->integer()->unsigned()->null()->comment('Foreign key to quest table'),
            'player_id' => $this->integer()->unsigned()->null()->comment('Foreign key to player table'),
            'client_id' => $this->string(64)->null()->comment('Connection ID (socket)'),
            'last_ts' => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('Last time the session was used'),
        ]);

        $this->createIndex('uk_client', '{{%quest_session}}', 'client_id', true);
        $this->addForeignKey('quest_session_ibfk_1', '{{%quest_session}}', 'player_id', '{{%player}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('quest_session_ibfk_2', '{{%quest_session}}', 'quest_id', '{{%quest}}', 'id', 'CASCADE', 'CASCADE');
    }
}
