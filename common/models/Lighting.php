<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "lighting".
 *
 * @property int $id Primary key
 * @property string $name Lighting
 * @property string|null $description Short description
 *
 * @property Tile[] $tiles
 */
class Lighting extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'lighting';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['name'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 32],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'name' => 'Lighting',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[Tiles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTiles() {
        return $this->hasMany(Tile::class, ['lighting_id' => 'id']);
    }
}
