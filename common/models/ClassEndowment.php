<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "class_endowment".
 *
 * @property int $id Primary key
 * @property int $class_id Foreign key to "class" table
 * @property int $choice Choice number
 * @property int $sort_order Sort order
 * @property string|null $name Name
 *
 * @property CharacterClass $class
 * @property ClassEquipment[] $classEquipments
 */
class ClassEndowment extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'class_endowment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['class_id'], 'required'],
            [['class_id', 'choice', 'sort_order'], 'integer'],
            [['name'], 'string', 'max' => 32],
            [['class_id'], 'exist', 'skipOnError' => true, 'targetClass' => CharacterClass::class, 'targetAttribute' => ['class_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'class_id' => 'Foreign key to \"class\" table',
            'choice' => 'Choice number',
            'sort_order' => 'Sort order',
            'name' => 'Name',
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
     * Gets query for [[ClassEquipments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClassEquipments() {
        return $this->hasMany(ClassEquipment::class, ['endowment_id' => 'id']);
    }
}
