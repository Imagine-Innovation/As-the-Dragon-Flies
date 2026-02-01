<?php

namespace common\models;

use common\helpers\RichTextHelper;
use Yii;

/**
 * This is the model class for table "feature".
 *
 * @property int $id Primary key
 * @property string $name Feature
 * @property string|null $description Short description
 *
 * @property ClassFeature[] $classFeatures
 */
class Feature extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'feature';
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
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Primary key',
            'name' => 'Feature',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[ClassFeatures]].
     *
     * @return \yii\db\ActiveQuery<ClassFeature>
     */
    public function getClassFeatures()
    {
        return $this->hasMany(ClassFeature::class, ['feature_id' => 'id']);
    }
}
