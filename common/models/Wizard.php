<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "wizard".
 *
 * @property int $id Primary key
 * @property string $name Wizard
 * @property string $topic Type of attribute defined by the Wizard (class, race...)
 *
 * @property WizardQuestion[] $wizardQuestions
 */
class Wizard extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'wizard';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'topic'], 'required'],
            [['name', 'topic'], 'string', 'max' => 64],
            [['topic'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Primary key',
            'name' => 'Wizard',
            'topic' => 'Type of attribute defined by the Wizard (class, race...)',
        ];
    }

    /**
     * Gets query for [[WizardQuestions]].
     *
     * @return \yii\db\ActiveQuery<WizardQuestion>
     */
    public function getWizardQuestions()
    {
        return $this->hasMany(WizardQuestion::class, ['wizard_id' => 'id']);
    }
}
