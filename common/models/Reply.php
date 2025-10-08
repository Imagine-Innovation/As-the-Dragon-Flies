<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "reply".
 *
 * @property int $id Primary key
 * @property int $dialog_id Foreign key to “dialog” table
 * @property int|null $next_dialog_id Optional foreign key to “dialog” table
 * @property string|null $text Question
 *
 * @property Action[] $actions
 * @property Dialog $dialog
 * @property Dialog $nextDialog
 */
class Reply extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'reply';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['next_dialog_id', 'text'], 'default', 'value' => null],
            [['dialog_id'], 'required'],
            [['dialog_id', 'next_dialog_id'], 'integer'],
            [['text'], 'string', 'max' => 255],
            [['dialog_id'], 'exist', 'skipOnError' => true, 'targetClass' => Dialog::class, 'targetAttribute' => ['dialog_id' => 'id']],
            [['next_dialog_id'], 'exist', 'skipOnError' => true, 'targetClass' => Dialog::class, 'targetAttribute' => ['next_dialog_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'dialog_id' => 'Foreign key to “dialog” table',
            'next_dialog_id' => 'Optional foreign key to “dialog” table',
            'text' => 'Question',
        ];
    }

    /**
     * Gets query for [[Actions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getActions() {
        return $this->hasMany(Action::class, ['reply_id' => 'id']);
    }

    /**
     * Gets query for [[Dialog]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDialog() {
        return $this->hasOne(Dialog::class, ['id' => 'dialog_id']);
    }

    /**
     * Gets query for [[NextDialog]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNextDialog() {
        return $this->hasOne(Dialog::class, ['id' => 'next_dialog_id']);
    }

}
