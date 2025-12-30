<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "proficiency".
 *
 * @property int $id Primary key
 * @property string $name Proficiency
 * @property int $sort_order Sort order
 * @property string|null $description Short description
 *
 * @property ClassProficiency[] $classProficiencies
 */
class Proficiency extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'proficiency';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['description'], 'default', 'value' => null],
            [['name', 'sort_order'], 'required'],
            [['sort_order'], 'integer'],
            [['description'], 'string'],
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
            'name' => 'Proficiency',
            'sort_order' => 'Sort order',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[ClassProficiencies]].
     *
     * @return \yii\db\ActiveQuery<ClassProficiency>
     */
    public function getClassProficiencies() {
        return $this->hasMany(ClassProficiency::class, ['proficiency_id' => 'id']);
    }
}
