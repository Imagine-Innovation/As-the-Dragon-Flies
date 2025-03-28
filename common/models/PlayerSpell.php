<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "player_spell".
 *
 * @property int $player_id Foreign key to "player" table
 * @property int $spell_id Foreign key to "spell" table
 *
 * @property Player $player
 * @property Spell $spell
 */
class PlayerSpell extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'player_spell';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['player_id', 'spell_id'], 'required'],
            [['player_id', 'spell_id'], 'integer'],
            [['player_id', 'spell_id'], 'unique', 'targetAttribute' => ['player_id', 'spell_id']],
            [['player_id'], 'exist', 'skipOnError' => true, 'targetClass' => Player::class, 'targetAttribute' => ['player_id' => 'id']],
            [['spell_id'], 'exist', 'skipOnError' => true, 'targetClass' => Spell::class, 'targetAttribute' => ['spell_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'player_id' => 'Foreign key to \"player\" table',
            'spell_id' => 'Foreign key to \"spell\" table',
        ];
    }

    /**
     * Gets query for [[Player]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayer() {
        return $this->hasOne(Player::class, ['id' => 'player_id']);
    }

    /**
     * Gets query for [[Spell]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSpell() {
        return $this->hasOne(Spell::class, ['id' => 'spell_id']);
    }
}
