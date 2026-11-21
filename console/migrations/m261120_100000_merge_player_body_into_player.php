<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Merges the `player_body` table into the `player` table.
 *
 * `player_body` was a strict 1:1 extension of `player` (its primary key was
 * the foreign key `player_id`). The five nullable equipment columns
 * (`head_item_id`, `chest_item_id`, `right_hand_item_id`, `left_hand_item_id`,
 * `back_item_id`) are moved onto `player`, together with their composite
 * foreign keys to `player_item (player_id, item_id)`, kept with
 * `ON DELETE NO ACTION` so an equipped item can never be deleted without
 * being disarmed first.
 */
class m261120_100000_merge_player_body_into_player extends Migration
{
    private const SLOTS = ['head_item_id', 'chest_item_id', 'right_hand_item_id', 'left_hand_item_id', 'back_item_id'];

    /**
     * Adds the equipment columns to `player`, copies the equipment data,
     * attaches the composite foreign keys and drops `player_body`.
     */
    public function up(): void
    {
        foreach (self::SLOTS as $i => $column) {
            $this->addColumn(
                '{{%player}}',
                $column,
                $this->integer()->unsigned()->null()->comment('Optional foreign key to “player_item” table. Item related to this body slot')
            );
            $this->createIndex('fk_' . ['head', 'chest', 'right_hand', 'left_hand', 'back'][$i], '{{%player}}', ['id', $column]);
        }

        $this->execute('UPDATE {{%player}} p
INNER JOIN {{%player_body}} pb ON pb.player_id = p.id
SET p.head_item_id = pb.head_item_id,
    p.chest_item_id = pb.chest_item_id,
    p.right_hand_item_id = pb.right_hand_item_id,
    p.left_hand_item_id = pb.left_hand_item_id,
    p.back_item_id = pb.back_item_id');

        foreach (self::SLOTS as $i => $column) {
            $this->addForeignKey(
                'player_ibfk_' . (9 + $i),
                '{{%player}}',
                ['id', $column],
                '{{%player_item}}',
                ['player_id', 'item_id'],
                'NO ACTION',
                'CASCADE'
            );
        }

        $this->dropTable('{{%player_body}}');
    }

    /**
     * Recreates `player_body` as it existed before the merge, copies the
     * equipment data back, then removes the columns and foreign keys from
     * `player`.
     */
    public function down(): void
    {
        $this->execute("CREATE TABLE {{%player_body}} (
  `player_id` int(10) unsigned NOT NULL COMMENT 'Primary key and foreign key to “player” table',
  `head_item_id` int(10) unsigned DEFAULT NULL COMMENT 'Optional foreign key to “player_item” table. Item that protect the head',
  `chest_item_id` int(10) unsigned DEFAULT NULL COMMENT 'Optional foreign key to “player_item” table. Item that protect the chest',
  `right_hand_item_id` int(10) unsigned DEFAULT NULL COMMENT 'Optional foreign key to “player_item” table. Item handled in the right hand',
  `left_hand_item_id` int(10) unsigned DEFAULT NULL COMMENT 'Optional foreign key to “player_item” table. Item handled in the left hand',
  `back_item_id` int(10) unsigned DEFAULT NULL COMMENT 'Optional foreign key to “player_item” table. Item carried on the back',
  PRIMARY KEY (`player_id`),
  KEY `fk_head` (`player_id`,`head_item_id`),
  KEY `fk_chest` (`player_id`,`chest_item_id`),
  KEY `fk_right_hand` (`player_id`,`right_hand_item_id`),
  KEY `fk_left_hand` (`player_id`,`left_hand_item_id`),
  KEY `fk_back` (`player_id`,`back_item_id`),
  KEY `fk_player` (`player_id`) USING BTREE,
  CONSTRAINT `player_body_ibfk_01` FOREIGN KEY (`player_id`) REFERENCES `player` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `player_body_ibfk_2` FOREIGN KEY (`player_id`, `head_item_id`) REFERENCES `player_item` (`player_id`, `item_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `player_body_ibfk_3` FOREIGN KEY (`player_id`, `chest_item_id`) REFERENCES `player_item` (`player_id`, `item_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `player_body_ibfk_4` FOREIGN KEY (`player_id`, `right_hand_item_id`) REFERENCES `player_item` (`player_id`, `item_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `player_body_ibfk_5` FOREIGN KEY (`player_id`, `left_hand_item_id`) REFERENCES `player_item` (`player_id`, `item_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `player_body_ibfk_6` FOREIGN KEY (`player_id`, `back_item_id`) REFERENCES `player_item` (`player_id`, `item_id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Connects the players with the items they are equipped with.'");

        $this->execute('INSERT INTO {{%player_body}} (player_id, head_item_id, chest_item_id, right_hand_item_id, left_hand_item_id, back_item_id)
SELECT id, head_item_id, chest_item_id, right_hand_item_id, left_hand_item_id, back_item_id
FROM {{%player}}');

        foreach (array_reverse(self::SLOTS) as $i => $column) {
            $index = 9 + (count(self::SLOTS) - 1 - $i);
            $this->dropForeignKey('player_ibfk_' . $index, '{{%player}}');
            $this->dropIndex('fk_' . ['head', 'chest', 'right_hand', 'left_hand', 'back'][count(self::SLOTS) - 1 - $i], '{{%player}}');
            $this->dropColumn('{{%player}}', $column);
        }
    }
}
