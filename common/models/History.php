<?php

namespace common\models;

use common\helpers\RichTextHelper;
use Yii;

/**
 * This is the model class for table "history".
 *
 * @property int $id Primary key
 * @property string $name History
 * @property string|null $description Short description
 */
class History extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'history';
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
            'name' => 'History',
            'description' => 'Short description',
        ];
    }
}
