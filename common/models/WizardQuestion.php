<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "wizard_question".
 *
 * @property int $id Primary key
 * @property int $wizard_id Foreign key to "wizard" table
 * @property string $question Question to be asked
 * @property int $is_first_question Indicates that this question is the entry point for a wizard
 *
 * @property Wizard $wizard
 * @property WizardAnswer[] $wizardAnswers
 * @property WizardAnswer[] $wizardAnswers0
 */
class WizardQuestion extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'wizard_question';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['wizard_id', 'question'], 'required'],
            [['wizard_id', 'is_first_question'], 'integer'],
            [['question'], 'string', 'max' => 255],
            [['wizard_id'], 'exist', 'skipOnError' => true, 'targetClass' => Wizard::class, 'targetAttribute' => ['wizard_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'wizard_id' => 'Foreign key to \"wizard\" table',
            'question' => 'Question to be asked',
            'is_first_question' => 'Indicates that this question is the entry point for a wizard',
        ];
    }

    /**
     * Gets query for [[Wizard]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWizard() {
        return $this->hasOne(Wizard::class, ['id' => 'wizard_id']);
    }

    /**
     * Gets query for [[WizardAnswers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWizardAnswers() {
        return $this->hasMany(WizardAnswer::class, ['question_id' => 'id']);
    }

    /**
     * Gets query for [[WizardAnswers0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWizardAnswers0() {
        return $this->hasMany(WizardAnswer::class, ['next_question_id' => 'id']);
    }
}
