<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "entry_point".
 *
 * @property int $story_id Foreign key to "story" table
 * @property int $tile_id Foreign key to "tile" table
 *
 * @property Story $story
 * @property Tile $tile
 */
class EntryPoint extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'entry_point';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['story_id', 'tile_id'], 'required'],
            [['story_id', 'tile_id'], 'integer'],
            [['story_id', 'tile_id'], 'unique', 'targetAttribute' => ['story_id', 'tile_id']],
            [['story_id'], 'exist', 'skipOnError' => true, 'targetClass' => Story::class, 'targetAttribute' => ['story_id' => 'id']],
            [['tile_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tile::class, 'targetAttribute' => ['tile_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'story_id' => 'Foreign key to \"story\" table',
            'tile_id' => 'Foreign key to \"tile\" table',
        ];
    }

    /**
     * Gets query for [[Story]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStory() {
        return $this->hasOne(Story::class, ['id' => 'story_id']);
    }

    /**
     * Gets query for [[Tile]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTile() {
        return $this->hasOne(Tile::class, ['id' => 'tile_id']);
    }
}
