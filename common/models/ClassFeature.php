<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "class_feature".
 *
 * @property int $id Primary key
 * @property int $class_id Foreign key to "class" table
 * @property int $feature_id Foreign key to "feature" table
 * @property int $level_id Foreign key to "level" table
 * @property float|null $cr Challenge Rating is a numerical value assigned to a creature or non-player character (NPC) to indicate the level of challenge it presents to player characters (PCs) in terms of combat and other encounters.
 * @property string|null $dice Special modifier for bardic features
 * @property int|null $weapon_dice Number of additionnal weapon dice to be thrown
 * @property int $times_used Number of time a feature can be used
 * @property int|null $spell_level Spell level
 *
 * @property Class $class
 * @property Feature $feature
 * @property Level $level
 */
class ClassFeature extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'class_feature';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['class_id', 'feature_id', 'level_id'], 'required'],
            [['class_id', 'feature_id', 'level_id', 'weapon_dice', 'times_used', 'spell_level'], 'integer'],
            [['cr'], 'number'],
            [['dice'], 'string', 'max' => 8],
            [['class_id'], 'exist', 'skipOnError' => true, 'targetClass' => CharacterClass::class, 'targetAttribute' => ['class_id' => 'id']],
            [['level_id'], 'exist', 'skipOnError' => true, 'targetClass' => Level::class, 'targetAttribute' => ['level_id' => 'id']],
            [['feature_id'], 'exist', 'skipOnError' => true, 'targetClass' => Feature::class, 'targetAttribute' => ['feature_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'class_id' => 'Foreign key to \"class\" table',
            'feature_id' => 'Foreign key to \"feature\" table',
            'level_id' => 'Foreign key to \"level\" table',
            'cr' => 'Challenge Rating is a numerical value assigned to a creature or non-player character (NPC) to indicate the level of challenge it presents to player characters (PCs) in terms of combat and other encounters.',
            'dice' => 'Special modifier for bardic features',
            'weapon_dice' => 'Number of additionnal weapon dice to be thrown',
            'times_used' => 'Number of time a feature can be used',
            'spell_level' => 'Spell level',
        ];
    }

    /**
     * Gets query for [[Class]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClass() {
        return $this->hasOne(CharacterClass::class, ['id' => 'class_id']);
    }

    /**
     * Gets query for [[Feature]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFeature() {
        return $this->hasOne(Feature::class, ['id' => 'feature_id']);
    }

    /**
     * Gets query for [[Level]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLevel() {
        return $this->hasOne(Level::class, ['id' => 'level_id']);
    }
}
