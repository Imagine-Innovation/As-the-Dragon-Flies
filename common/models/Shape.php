<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "shape".
 *
 * @property int $id Primary key
 * @property int $size_id Foreign key to “creature_size” table
 * @property int $creature_id Foreign key to “creature” table
 * @property string $name Creature shape
 * @property int|null $ac Armor class
 * @property int $languages Number of languages the creature can understand and speak if the “can_speak” flag is set to TRUE
 * @property int $can_speak Indicates whether the creature can speak or not in this shape
 * @property int $is_telepath Indicates that the creature is telepath in that shate
 * @property int|null $telepathy_range Distance at which the creature can use telepathy (ft.)
 * @property int|null $passive_perception When you hide, there's a chance someone will notice you even if they aren't searching. To determine whether such a creature notices you, the DM compares your Dexterity (Stealth) check with that creature's passive Wisdom (Perception) score
 * @property int|null $blindsight A creature with blindsight can perceive its surroundings without relying on sight, within a specific radius
 * @property int|null $darkvision Within a specified range, a creature with darkvision can see in darkness as if the darkness were dim light, so areas of darkness are only lightly obscured as far as that creature is concerned. However, the creature can’t discern color in darkness, only shades of gray
 * @property int|null $tremorsense A creature with tremorsense can detect and pinpoint the origin of vibrations within a specific radius, provided that the creature and the source of the vibrations are in contact with the same ground or substance. Tremorsense can't be used to detect flying or incorporeal creatures
 *
 * @property Armor[] $armors
 * @property Creature $creature
 * @property Language[] $languages0
 * @property Movement[] $movements
 * @property ShapeArmor[] $shapeArmors
 * @property ShapeAttack[] $shapeAttacks
 * @property ShapeLanguage[] $shapeLanguages
 * @property ShapeMovement[] $shapeMovements
 * @property CreatureSize $size
 */
class Shape extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'shape';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['ac', 'telepathy_range', 'passive_perception', 'blindsight', 'darkvision', 'tremorsense'], 'default', 'value' => null],
            [['is_telepath'], 'default', 'value' => 0],
            [['can_speak'], 'default', 'value' => 1],
            [['size_id', 'creature_id', 'name'], 'required'],
            [['size_id', 'creature_id', 'ac', 'languages', 'can_speak', 'is_telepath', 'telepathy_range', 'passive_perception', 'blindsight', 'darkvision', 'tremorsense'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [['creature_id'], 'exist', 'skipOnError' => true, 'targetClass' => Creature::class, 'targetAttribute' => ['creature_id' => 'id']],
            [['size_id'], 'exist', 'skipOnError' => true, 'targetClass' => CreatureSize::class, 'targetAttribute' => ['size_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'size_id' => 'Foreign key to “creature_size” table',
            'creature_id' => 'Foreign key to “creature” table',
            'name' => 'Creature shape',
            'ac' => 'Armor class',
            'languages' => 'Number of languages the creature can understand and speak if the “can_speak” flag is set to TRUE',
            'can_speak' => 'Indicates whether the creature can speak or not in this shape',
            'is_telepath' => 'Indicates that the creature is telepath in that shate',
            'telepathy_range' => 'Distance at which the creature can use telepathy (ft.)',
            'passive_perception' => 'When you hide, there\'s a chance someone will notice you even if they aren\'t searching. To determine whether such a creature notices you, the DM compares your Dexterity (Stealth) check with that creature\'s passive Wisdom (Perception) score',
            'blindsight' => 'A creature with blindsight can perceive its surroundings without relying on sight, within a specific radius',
            'darkvision' => 'Within a specified range, a creature with darkvision can see in darkness as if the darkness were dim light, so areas of darkness are only lightly obscured as far as that creature is concerned. However, the creature can’t discern color in darkness, only shades of gray',
            'tremorsense' => 'A creature with tremorsense can detect and pinpoint the origin of vibrations within a specific radius, provided that the creature and the source of the vibrations are in contact with the same ground or substance. Tremorsense can\'t be used to detect flying or incorporeal creatures',
        ];
    }

    /**
     * Gets query for [[Armors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getArmors() {
        return $this->hasMany(Armor::class, ['item_id' => 'armor_id'])->viaTable('shape_armor', ['shape_id' => 'id']);
    }

    /**
     * Gets query for [[Creature]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreature() {
        return $this->hasOne(Creature::class, ['id' => 'creature_id']);
    }

    /**
     * Gets query for [[Languages0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLanguages0() {
        return $this->hasMany(Language::class, ['id' => 'language_id'])->viaTable('shape_language', ['shape_id' => 'id']);
    }

    /**
     * Gets query for [[Movements]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMovements() {
        return $this->hasMany(Movement::class, ['id' => 'movement_id'])->viaTable('shape_movement', ['shape_id' => 'id']);
    }

    /**
     * Gets query for [[ShapeArmors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShapeArmors() {
        return $this->hasMany(ShapeArmor::class, ['shape_id' => 'id']);
    }

    /**
     * Gets query for [[ShapeAttacks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShapeAttacks() {
        return $this->hasMany(ShapeAttack::class, ['shape_id' => 'id']);
    }

    /**
     * Gets query for [[ShapeLanguages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShapeLanguages() {
        return $this->hasMany(ShapeLanguage::class, ['shape_id' => 'id']);
    }

    /**
     * Gets query for [[ShapeMovements]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShapeMovements() {
        return $this->hasMany(ShapeMovement::class, ['shape_id' => 'id']);
    }

    /**
     * Gets query for [[Size]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSize() {
        return $this->hasOne(CreatureSize::class, ['id' => 'size_id']);
    }

}
