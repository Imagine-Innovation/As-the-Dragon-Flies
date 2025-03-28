<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "player_language".
 *
 * @property int $player_id Foreign key to "player" table
 * @property int $language_id Foreign key to "language" table
 *
 * @property Language $language
 * @property Player $player
 */
class PlayerLanguage extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'player_language';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['player_id', 'language_id'], 'required'],
            [['player_id', 'language_id'], 'integer'],
            [['player_id', 'language_id'], 'unique', 'targetAttribute' => ['player_id', 'language_id']],
            [['player_id'], 'exist', 'skipOnError' => true, 'targetClass' => Player::class, 'targetAttribute' => ['player_id' => 'id']],
            [['language_id'], 'exist', 'skipOnError' => true, 'targetClass' => Language::class, 'targetAttribute' => ['language_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'player_id' => 'Foreign key to \"player\" table',
            'language_id' => 'Foreign key to \"language\" table',
        ];
    }

    /**
     * Gets query for [[Language]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLanguage() {
        return $this->hasOne(Language::class, ['id' => 'language_id']);
    }

    /**
     * Gets query for [[Player]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayer() {
        return $this->hasOne(Player::class, ['id' => 'player_id']);
    }
}
