<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "level".
 *
 * @property int $id Primary key
 * @property string $name Level
 * @property int $xp_min XP min
 * @property int $xp_max XP max
 * @property int $proficiency_bonus Proficiency bonus
 *
 * @property ClassFeature[] $classFeatures
 * @property ClassProficiency[] $classProficiencies
 * @property Player[] $players
 */
class Level extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'level';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['proficiency_bonus'], 'default', 'value' => 0],
            [['name', 'xp_min', 'xp_max'], 'required'],
            [['xp_min', 'xp_max', 'proficiency_bonus'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'name' => 'Level',
            'xp_min' => 'XP min',
            'xp_max' => 'XP max',
            'proficiency_bonus' => 'Proficiency bonus',
        ];
    }

    /**
     * Gets query for [[ClassFeatures]].
     *
     * @return \yii\db\ActiveQuery<ClassFeature>
     */
    public function getClassFeatures() {
        return $this->hasMany(ClassFeature::class, ['level_id' => 'id']);
    }

    /**
     * Gets query for [[ClassProficiencies]].
     *
     * @return \yii\db\ActiveQuery<ClassProficiency>
     */
    public function getClassProficiencies() {
        return $this->hasMany(ClassProficiency::class, ['level_id' => 'id']);
    }

    /**
     * Gets query for [[Players]].
     *
     * @return \yii\db\ActiveQuery<Player>
     */
    public function getPlayers() {
        return $this->hasMany(Player::class, ['level_id' => 'id']);
    }
}
