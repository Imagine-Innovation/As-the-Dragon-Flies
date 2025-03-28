<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "background_history".
 *
 * @property int $id Primary key
 * @property string $name History
 * @property string|null $description Short description
 *
 * @property Player[] $players
 */
class BackgroundHistory extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'background_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
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
            'name' => 'History',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[Players]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayers() {
        return $this->hasMany(Player::class, ['history_id' => 'id']);
    }
}
