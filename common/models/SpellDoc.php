<?php

namespace common\models;

use common\helpers\RichTextHelper;
use Yii;

/**
 * This is the model class for table "spell_doc".
 *
 * @property int $id Primary key
 * @property int $spell_id Foreign key to “spell” table
 * @property string $name Chapter
 * @property int $sort_order Sort order
 * @property string|null $description Short description
 *
 * @property Spell $spell
 */
class SpellDoc extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'spell_doc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'default', 'value' => null],
            [['spell_id', 'name', 'sort_order'], 'required'],
            [['spell_id', 'sort_order'], 'integer'],
            [['description'], 'string'],
            [['description'], 'filter', 'filter' => [RichTextHelper::class, 'sanitizeWithCache']],
            [['name'], 'string', 'max' => 64],
            [['spell_id'], 'exist', 'skipOnError' => true, 'targetClass' => Spell::class, 'targetAttribute' => ['spell_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Primary key',
            'spell_id' => 'Foreign key to “spell” table',
            'name' => 'Chapter',
            'sort_order' => 'Sort order',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[Spell]].
     *
     * @return \yii\db\ActiveQuery<Spell>
     */
    public function getSpell()
    {
        return $this->hasOne(Spell::class, ['id' => 'spell_id']);
    }
}
