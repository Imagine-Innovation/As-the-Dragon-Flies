<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "alignment".
 *
 * @property int $id Primary key
 * @property string $code Alignment code ("LG" for "Lawful Goog"...)
 * @property string $name Alignment (e.g., "Chaotic Good," "Lawful Evil"). You can choose an alignment that you believe represents the common tendencies or cultural norms of that race in your campaign setting.
 * @property string|null $description A textual description explaining the suggested alignment and providing context for why members of the race tend to exhibit these traits.
 *
 * @property CreatureAlignment[] $creatureAlignments
 * @property Creature[] $creatures
 * @property Player[] $players
 * @property RaceGroupAlignment[] $raceGroupAlignments
 * @property RaceGroup[] $raceGroups
 */
class Alignment extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'alignment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['code', 'name'], 'required'],
            [['description'], 'string'],
            [['code'], 'string', 'max' => 4],
            [['name'], 'string', 'max' => 32],
            [['code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'code' => 'Alignment code (\"LG\" for \"Lawful Goog\"...)',
            'name' => 'Alignment (e.g., \"Chaotic Good,\" \"Lawful Evil\"). You can choose an alignment that you believe represents the common tendencies or cultural norms of that race in your campaign setting.',
            'description' => 'A textual description explaining the suggested alignment and providing context for why members of the race tend to exhibit these traits.',
        ];
    }

    /**
     * Gets query for [[CreatureAlignments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatureAlignments() {
        return $this->hasMany(CreatureAlignment::class, ['alignment_id' => 'id']);
    }

    /**
     * Gets query for [[Creatures]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatures() {
        return $this->hasMany(Creature::class, ['id' => 'creature_id'])->via('creatureAlignments');
    }

    /**
     * Gets query for [[Players]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayers() {
        return $this->hasMany(Player::class, ['alignment_id' => 'id']);
    }

    /**
     * Gets query for [[RaceGroupAlignments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRaceGroupAlignments() {
        return $this->hasMany(RaceGroupAlignment::class, ['alignment_id' => 'id']);
    }

    /**
     * Gets query for [[RaceGroups]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRaceGroups() {
        return $this->hasMany(RaceGroup::class, ['id' => 'race_group_id'])->via('raceGroupAlignments');
    }
}
