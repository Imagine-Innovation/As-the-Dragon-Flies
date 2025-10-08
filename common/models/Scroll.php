<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "scroll".
 *
 * @property int $item_id Primary key
 * @property int $language_id Foreign key to “language” table
 * @property string|null $text Scroll text
 *
 * @property Item $item
 * @property Language $language
 */
class Scroll extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'scroll';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['text'], 'default', 'value' => null],
            [['language_id'], 'required'],
            [['language_id'], 'integer'],
            [['text'], 'string'],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['item_id' => 'id']],
            [['language_id'], 'exist', 'skipOnError' => true, 'targetClass' => Language::class, 'targetAttribute' => ['language_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'item_id' => 'Primary key',
            'language_id' => 'Foreign key to “language” table',
            'text' => 'Scroll text',
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
     * Gets query for [[Language]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLanguage() {
        return $this->hasOne(Language::class, ['id' => 'language_id']);
    }

}
