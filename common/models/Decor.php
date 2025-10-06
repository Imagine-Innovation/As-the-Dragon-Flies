<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "decor".
 *
 * @property int $id Primary key
 * @property int $mission_id Foreign key to "mission" table
 * @property int|null $item_id Optional foreign key to “item” table. Item that can be found in the decor element.
 * @property int|null $trap_id Optional foreign key to "Trap" table. Trap that can be found in the decor element.
 * @property string $name Decor
 * @property string $description Short description
 *
 * @property Item $item
 * @property Mission $mission
 * @property Trap $trap
 */
class Decor extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'decor';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['item_id', 'trap_id'], 'default', 'value' => null],
            [['mission_id', 'name', 'description'], 'required'],
            [['mission_id', 'item_id', 'trap_id'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 32],
            [['mission_id'], 'exist', 'skipOnError' => true, 'targetClass' => Mission::class, 'targetAttribute' => ['mission_id' => 'id']],
            [['trap_id'], 'exist', 'skipOnError' => true, 'targetClass' => Trap::class, 'targetAttribute' => ['trap_id' => 'id']],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['item_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'mission_id' => 'Foreign key to \"mission\" table',
            'item_id' => 'Optional foreign key to “item” table. Item that can be found in the decor element.',
            'trap_id' => 'Optional foreign key to \"Trap\" table. Trap that can be found in the decor element.',
            'name' => 'Decor',
            'description' => 'Short description',
        ];
    }

    /**
     * Gets query for [[Item]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItem() {
        return $this->hasOne(Item::class, ['id' => 'item_id']);
    }

    /**
     * Gets query for [[Mission]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMission() {
        return $this->hasOne(Mission::class, ['id' => 'mission_id']);
    }

    /**
     * Gets query for [[Trap]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTrap() {
        return $this->hasOne(Trap::class, ['id' => 'trap_id']);
    }

}
