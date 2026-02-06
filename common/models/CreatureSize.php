<?php

namespace common\models;

use common\helpers\RichTextHelper;
use Yii;

/**
 * This is the model class for table "creature_size".
 *
 * @property int $id Primary key
 * @property string $name Creature size
 * @property string|null $description Short description
 *
 * @property Shape[] $shapes
 */
class CreatureSize extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'creature_size';
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
            'name' => 'Creature size',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[Shapes]].
     *
     * @return \yii\db\ActiveQuery<Shape>
     */
    public function getShapes()
    {
        return $this->hasMany(Shape::class, ['size_id' => 'id']);
    }
}
