<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "spell_component".
 *
 * @property int $spell_id Foreign key to “spell” table
 * @property int $component_id Foreign key to “component” table
 * @property string|null $material Short description
 *
 * @property Component $component
 * @property Spell $spell
 */
class SpellComponent extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'spell_component';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['material'], 'default', 'value' => null],
            [['spell_id', 'component_id'], 'required'],
            [['spell_id', 'component_id'], 'integer'],
            [['material'], 'string'],
            [['spell_id', 'component_id'], 'unique', 'targetAttribute' => ['spell_id', 'component_id']],
            [['spell_id'], 'exist', 'skipOnError' => true, 'targetClass' => Spell::class, 'targetAttribute' => ['spell_id' => 'id']],
            [['component_id'], 'exist', 'skipOnError' => true, 'targetClass' => Component::class, 'targetAttribute' => ['component_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'spell_id' => 'Foreign key to “spell” table',
            'component_id' => 'Foreign key to “component” table',
            'material' => 'Short description',
        ];
    }

    /**
     * Gets query for [[Component]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getComponent() {
        return $this->hasOne(Component::class, ['id' => 'component_id']);
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
