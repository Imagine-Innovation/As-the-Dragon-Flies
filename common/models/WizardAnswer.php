<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "wizard_answer".
 *
 * @property int $id Primary key
 * @property int $question_id Foreign key to “wizard_question” table
 * @property string $answer Possible answer
 * @property int|null $next_question_id Optional foreign key to “wizard_question” table. Used only id non terminal question.
 * @property int|null $class_id Optional foreign key to “character_class” table. Used only when the wizard helps defining a class
 * @property int|null $race_id Optional foreign key to “race” table. Used only when the wizard helps defining a race
 * @property int|null $alignment_id Optional foreign key to “alignment” table. Used only when the wizard helps defining an alignment
 *
 * @property Alignment $alignment
 * @property CharacterClass $class
 * @property WizardQuestion $nextQuestion
 * @property WizardQuestion $question
 * @property Race $race
 */
class WizardAnswer extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'wizard_answer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['next_question_id', 'class_id', 'race_id', 'alignment_id'], 'default', 'value' => null],
            [['question_id', 'answer'], 'required'],
            [['question_id', 'next_question_id', 'class_id', 'race_id', 'alignment_id'], 'integer'],
            [['answer'], 'string', 'max' => 255],
            [['question_id'], 'exist', 'skipOnError' => true, 'targetClass' => WizardQuestion::class, 'targetAttribute' => ['question_id' => 'id']],
            [['next_question_id'], 'exist', 'skipOnError' => true, 'targetClass' => WizardQuestion::class, 'targetAttribute' => ['next_question_id' => 'id']],
            [['class_id'], 'exist', 'skipOnError' => true, 'targetClass' => CharacterClass::class, 'targetAttribute' => ['class_id' => 'id']],
            [['race_id'], 'exist', 'skipOnError' => true, 'targetClass' => Race::class, 'targetAttribute' => ['race_id' => 'id']],
            [['alignment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Alignment::class, 'targetAttribute' => ['alignment_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'question_id' => 'Foreign key to “wizard_question” table',
            'answer' => 'Possible answer',
            'next_question_id' => 'Optional foreign key to “wizard_question” table. Used only id non terminal question.',
            'class_id' => 'Optional foreign key to “character_class” table. Used only when the wizard helps defining a class',
            'race_id' => 'Optional foreign key to “race” table. Used only when the wizard helps defining a race',
            'alignment_id' => 'Optional foreign key to “alignment” table. Used only when the wizard helps defining an alignment',
        ];
    }

    /**
     * Gets query for [[Alignment]].
     *
     * @return \yii\db\ActiveQuery<Alignment>
     */
    public function getAlignment() {
        return $this->hasOne(Alignment::class, ['id' => 'alignment_id']);
    }

    /**
     * Gets query for [[Class]].
     *
     * @return \yii\db\ActiveQuery<CharacterClass>
     */
    public function getClass() {
        return $this->hasOne(CharacterClass::class, ['id' => 'class_id']);
    }

    /**
     * Gets query for [[NextQuestion]].
     *
     * @return \yii\db\ActiveQuery<WizardQuestion>
     */
    public function getNextQuestion() {
        return $this->hasOne(WizardQuestion::class, ['id' => 'next_question_id']);
    }

    /**
     * Gets query for [[Question]].
     *
     * @return \yii\db\ActiveQuery<WizardQuestion>
     */
    public function getQuestion() {
        return $this->hasOne(WizardQuestion::class, ['id' => 'question_id']);
    }

    /**
     * Gets query for [[Race]].
     *
     * @return \yii\db\ActiveQuery<Race>
     */
    public function getRace() {
        return $this->hasOne(Race::class, ['id' => 'race_id']);
    }
}
