<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "language".
 *
 * @property int $id Primary key
 * @property string $name Language
 * @property string|null $description Short description
 *
 * @property PlayerLanguage[] $playerLanguages
 * @property Player[] $players
 * @property RaceGroupLanguage[] $raceGroupLanguages
 * @property RaceGroup[] $raceGroups
 * @property ShapeLanguage[] $shapeLanguages
 * @property Shape[] $shapes
 */
class Language extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'language';
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
            'name' => 'Language',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[PlayerLanguages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerLanguages() {
        return $this->hasMany(PlayerLanguage::class, ['language_id' => 'id']);
    }

    /**
     * Gets query for [[Players]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayers() {
        return $this->hasMany(Player::class, ['id' => 'player_id'])->via('playerLanguages');
    }

    /**
     * Gets query for [[RaceGroupLanguages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRaceGroupLanguages() {
        return $this->hasMany(RaceGroupLanguage::class, ['language_id' => 'id']);
    }

    /**
     * Gets query for [[RaceGroups]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRaceGroups() {
        return $this->hasMany(RaceGroup::class, ['id' => 'race_group_id'])->via('raceGroupLanguages');
    }

    /**
     * Gets query for [[ShapeLanguages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShapeLanguages() {
        return $this->hasMany(ShapeLanguage::class, ['language_id' => 'id']);
    }

    /**
     * Gets query for [[Shapes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getShapes() {
        return $this->hasMany(Shape::class, ['id' => 'shape_id'])->via('shapeLanguages');
    }
}
