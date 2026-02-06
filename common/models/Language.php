<?php

namespace common\models;

use common\helpers\RichTextHelper;
use Yii;

/**
 * This is the model class for table "language".
 *
 * @property int $id Primary key
 * @property string $name Language
 * @property string|null $description Short description
 *
 * @property Npc[] $npcs
 * @property PlayerLanguage[] $playerLanguages
 * @property Player[] $players
 * @property RaceGroupLanguage[] $raceGroupLanguages
 * @property RaceGroup[] $raceGroups
 * @property Scroll[] $scrolls
 * @property ShapeLanguage[] $shapeLanguages
 * @property Shape[] $shapes
 */
class Language extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'language';
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
            'name' => 'Language',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[Npcs]].
     *
     * @return \yii\db\ActiveQuery<Npc>
     */
    public function getNpcs()
    {
        return $this->hasMany(Npc::class, ['language_id' => 'id']);
    }

    /**
     * Gets query for [[PlayerLanguages]].
     *
     * @return \yii\db\ActiveQuery<PlayerLanguage>
     */
    public function getPlayerLanguages()
    {
        return $this->hasMany(PlayerLanguage::class, ['language_id' => 'id']);
    }

    /**
     * Gets query for [[Players]].
     *
     * @return \yii\db\ActiveQuery<Player>
     */
    public function getPlayers()
    {
        return $this->hasMany(Player::class, ['id' => 'player_id'])->viaTable('player_language', [
            'language_id' => 'id',
        ]);
    }

    /**
     * Gets query for [[RaceGroupLanguages]].
     *
     * @return \yii\db\ActiveQuery<RaceGroupLanguage>
     */
    public function getRaceGroupLanguages()
    {
        return $this->hasMany(RaceGroupLanguage::class, ['language_id' => 'id']);
    }

    /**
     * Gets query for [[RaceGroups]].
     *
     * @return \yii\db\ActiveQuery<RaceGroup>
     */
    public function getRaceGroups()
    {
        return $this->hasMany(RaceGroup::class, ['id' => 'race_group_id'])->viaTable('race_group_language', [
            'language_id' => 'id',
        ]);
    }

    /**
     * Gets query for [[Scrolls]].
     *
     * @return \yii\db\ActiveQuery<Scroll>
     */
    public function getScrolls()
    {
        return $this->hasMany(Scroll::class, ['language_id' => 'id']);
    }

    /**
     * Gets query for [[ShapeLanguages]].
     *
     * @return \yii\db\ActiveQuery<ShapeLanguage>
     */
    public function getShapeLanguages()
    {
        return $this->hasMany(ShapeLanguage::class, ['language_id' => 'id']);
    }

    /**
     * Gets query for [[Shapes]].
     *
     * @return \yii\db\ActiveQuery<Shape>
     */
    public function getShapes()
    {
        return $this->hasMany(Shape::class, ['id' => 'shape_id'])->viaTable('shape_language', ['language_id' => 'id']);
    }
}
