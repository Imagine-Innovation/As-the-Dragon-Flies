<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "trap".
 *
 * @property int $id Primary key
 * @property int $tile_id Foreign key to "tile" table
 * @property int $damage_type_id Foreign key to "damage_type" table
 * @property string $name Trap
 * @property int $present Indicates the probability that the part is present in the room
 * @property int $falling Indicates the probability of the player falling into the trap
 * @property string $damage Damage dice
 * @property int $is_team_trap Indicates that the whole team is trapped
 *
 * @property DamageType $damageType
 * @property Tile $tile
 */
class Trap extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'trap';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['tile_id', 'damage_type_id', 'name', 'damage'], 'required'],
            [['tile_id', 'damage_type_id', 'present', 'falling', 'is_team_trap'], 'integer'],
            [['name'], 'string', 'max' => 32],
            [['damage'], 'string', 'max' => 8],
            [['tile_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tile::class, 'targetAttribute' => ['tile_id' => 'id']],
            [['damage_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => DamageType::class, 'targetAttribute' => ['damage_type_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'tile_id' => 'Foreign key to \"tile\" table',
            'damage_type_id' => 'Foreign key to \"damage_type\" table',
            'name' => 'Trap',
            'present' => 'Indicates the probability that the part is present in the room',
            'falling' => 'Indicates the probability of the player falling into the trap',
            'damage' => 'Damage dice',
            'is_team_trap' => 'Indicates that the whole team is trapped',
        ];
    }

    /**
     * Gets query for [[DamageType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDamageType() {
        return $this->hasOne(DamageType::class, ['id' => 'damage_type_id']);
    }

    /**
     * Gets query for [[Tile]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTile() {
        return $this->hasOne(Tile::class, ['id' => 'tile_id']);
    }
}
