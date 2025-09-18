<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sequence".
 *
 * @property int $id Primary key
 * @property int $chapter_id Foreign key to "chapter" table
 * @property string $name Sequence
 * @property string|null $description Short description
 *
 * @property Chapter $chapter
 * @property Mission[] $missions
 */
class Sequence extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'sequence';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['description'], 'default', 'value' => null],
            [['chapter_id', 'name'], 'required'],
            [['chapter_id'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 32],
            [['chapter_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chapter::class, 'targetAttribute' => ['chapter_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'chapter_id' => 'Foreign key to \"chapter\" table',
            'name' => 'Sequence',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[Chapter]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChapter() {
        return $this->hasOne(Chapter::class, ['id' => 'chapter_id']);
    }

    /**
     * Gets query for [[Missions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMissions() {
        return $this->hasMany(Mission::class, ['sequence_id' => 'id']);
    }
}
