<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "creature_type".
 *
 * @property int $id Primary key
 * @property string $name Creature type
 * @property string|null $description Short description
 *
 * @property Creature[] $creatures
 */
class CreatureType extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'creature_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['name'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'name' => 'Creature type',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[Creatures]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatures() {
        return $this->hasMany(Creature::class, ['type_id' => 'id']);
    }
}
