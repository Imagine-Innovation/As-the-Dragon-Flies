<?php

namespace common\models;

use common\helpers\RichTextHelper;
use Yii;

/**
 * This is the model class for table "decor".
 *
 * @property int $id Primary key
 * @property int $mission_id Foreign key to “mission” table
 * @property string $name Decor
 * @property string|null $description Short description
 * @property string|null $image Image
 *
 * @property Action[] $actions
 * @property DecorItem[] $decorItems
 * @property Mission $mission
 * @property Trap[] $traps
 */
class Decor extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'decor';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description', 'image'], 'default', 'value' => null],
            [['mission_id', 'name'], 'required'],
            [['mission_id'], 'integer'],
            [['description'], 'string'],
            [['description'], 'filter', 'filter' => [RichTextHelper::class, 'sanitizeWithCache']],
            [['name', 'image'], 'string', 'max' => 64],
            [['mission_id'], 'exist', 'skipOnError' => true, 'targetClass' => Mission::class, 'targetAttribute' => ['mission_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Primary key',
            'mission_id' => 'Foreign key to “mission” table',
            'name' => 'Decor',
            'description' => 'Short description',
            'image' => 'Image',
        ];
    }

    /**
     * Gets query for [[Actions]].
     *
     * @return \yii\db\ActiveQuery<Action>
     */
    public function getActions()
    {
        return $this->hasMany(Action::class, ['decor_id' => 'id']);
    }

    /**
     * Gets query for [[DecorItems]].
     *
     * @return \yii\db\ActiveQuery<DecorItem>
     */
    public function getDecorItems()
    {
        return $this->hasMany(DecorItem::class, ['decor_id' => 'id']);
    }

    /**
     * Gets query for [[Mission]].
     *
     * @return \yii\db\ActiveQuery<Mission>
     */
    public function getMission()
    {
        return $this->hasOne(Mission::class, ['id' => 'mission_id']);
    }

    /**
     * Gets query for [[Traps]].
     *
     * @return \yii\db\ActiveQuery<Trap>
     */
    public function getTraps()
    {
        return $this->hasMany(Trap::class, ['decor_id' => 'id']);
    }
}
