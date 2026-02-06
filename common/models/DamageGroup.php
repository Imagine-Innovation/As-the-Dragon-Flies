<?php

namespace common\models;

use common\helpers\RichTextHelper;
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
    public static function tableName()
    {
        return 'damage_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'default', 'value' => null],
            [['name'], 'required'],
            [['description'], 'string'],
            [['description'], 'filter', 'filter' => [RichTextHelper::class, 'sanitizeWithCache']],
            [['name'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Primary key',
            'name' => 'Damage group',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[DamageTypes]].
     *
     * @return \yii\db\ActiveQuery<DamageType>
     */
    public function getDamageTypes()
    {
        return $this->hasMany(DamageType::class, ['group_id' => 'id']);
    }
}
