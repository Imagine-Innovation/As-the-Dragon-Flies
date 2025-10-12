<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "background".
 *
 * @property int $id Primary key
 * @property string $name Background
 * @property string|null $description Short description
 * @property string|null $initial_equipment Package of starting equipment given by the background
 * @property int|null $languages Number of languages to choose
 *
 * @property BackgroundAttribute[] $backgroundAttributes
 * @property BackgroundItem[] $backgroundItems
 * @property BackgroundSkill[] $backgroundSkills
 * @property BackgroundTrait[] $backgroundTraits
 * @property Player[] $players
 * @property Skill[] $skills
 */
class Background extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'background';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['description', 'initial_equipment', 'languages'], 'default', 'value' => null],
            [['name'], 'required'],
            [['description', 'initial_equipment'], 'string'],
            [['languages'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'name' => 'Background',
            'description' => 'Short description',
            'initial_equipment' => 'Package of starting equipment given by the background',
            'languages' => 'Number of languages to choose',
        ];
    }

    /**
     * Gets query for [[BackgroundAttributes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBackgroundAttributes() {
        return $this->hasMany(BackgroundAttribute::class, ['background_id' => 'id']);
    }

    /**
     * Gets query for [[BackgroundItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBackgroundItems() {
        return $this->hasMany(BackgroundItem::class, ['background_id' => 'id']);
    }

    /**
     * Gets query for [[BackgroundSkills]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBackgroundSkills() {
        return $this->hasMany(BackgroundSkill::class, ['background_id' => 'id']);
    }

    /**
     * Gets query for [[BackgroundTraits]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBackgroundTraits() {
        return $this->hasMany(BackgroundTrait::class, ['background_id' => 'id']);
    }

    /**
     * Gets query for [[Players]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayers() {
        return $this->hasMany(Player::class, ['background_id' => 'id']);
    }

    /**
     * Gets query for [[Skills]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSkills() {
        return $this->hasMany(Skill::class, ['id' => 'skill_id'])->viaTable('background_skill', ['background_id' => 'id']);
    }

}
