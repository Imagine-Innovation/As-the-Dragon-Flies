<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "passage".
 *
 * @property int $id Primary key
 * @property int $tile_from_id Foreign key to "tile" table to get the origine tile
 * @property int $tile_to_id Foreign key to "tile" table to get the destination tile
 * @property int $status_id Foreign key to "passage_status" table
 * @property string $name Passage
 * @property string|null $description Short description
 * @property int $found Give the probability of finding the passage (%)
 * @property string $passage_type Passage type. "D" for "Door", "C" for Corridor, "G" for Gate, "T" for tunnel, "P" for Portcullis, "B" for drawbridge
 *
 * @property PassageStatus $status
 * @property Tile $tileFrom
 * @property Tile $tileTo
 */
class Passage extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'passage';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['tile_from_id', 'tile_to_id', 'status_id', 'name'], 'required'],
            [['tile_from_id', 'tile_to_id', 'status_id', 'found'], 'integer'],
            [['description', 'passage_type'], 'string'],
            [['name'], 'string', 'max' => 32],
            [['tile_to_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tile::class, 'targetAttribute' => ['tile_to_id' => 'id']],
            [['tile_from_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tile::class, 'targetAttribute' => ['tile_from_id' => 'id']],
            [['status_id'], 'exist', 'skipOnError' => true, 'targetClass' => PassageStatus::class, 'targetAttribute' => ['status_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'tile_from_id' => 'Foreign key to \"tile\" table to get the origine tile',
            'tile_to_id' => 'Foreign key to \"tile\" table to get the destination tile',
            'status_id' => 'Foreign key to \"passage_status\" table',
            'name' => 'Passage',
            'description' => 'Short description',
            'found' => 'Give the probability of finding the passage (%)',
            'passage_type' => 'Passage type. \"D\" for \"Door\", \"C\" for Corridor, \"G\" for Gate, \"T\" for tunnel, \"P\" for Portcullis, \"B\" for drawbridge',
        ];
    }

    /**
     * Gets query for [[Status]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStatus() {
        return $this->hasOne(PassageStatus::class, ['id' => 'status_id']);
    }

    /**
     * Gets query for [[TileFrom]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTileFrom() {
        return $this->hasOne(Tile::class, ['id' => 'tile_from_id']);
    }

    /**
     * Gets query for [[TileTo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTileTo() {
        return $this->hasOne(Tile::class, ['id' => 'tile_to_id']);
    }
}
