<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "damage_group".
 *
 * @property int $id Primary key
 * @property string $name Damage group
 * @property string|null $description Short description
 *
 * @property DamageType[] $damageTypes
 */
class DamageGroup extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'damage_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['description'], 'default', 'value' => null],
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
            'name' => 'Damage group',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[DamageTypes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDamageTypes() {
        return $this->hasMany(DamageType::class, ['group_id' => 'id']);
    }

}
