<?php

namespace common\models;

use common\helpers\RichTextHelper;
use Yii;

/**
 * This is the model class for table "passage".
 *
 * @property int $id Primary key
 * @property int $decor_id Foreign key to “decor” table
 * @property int|null $to_decor_id Optional foreign key to “decor” table. Identify destination decor
 * @property string $name Passage
 * @property string|null $description Short description
 * @property int $passage_type Passage type: 1=door,2=gate,3=trapdoor,4=archway,5=portcullis,6=secret_passage,7=portal,8=window,9=hatch
 * @property int $openness Initial openness state: 0=open,1=ajar,2=closed,3=destroyed
 * @property int $is_locked Whether the passage starts locked
 * @property string|null $image Image
 *
 * @property Action[] $actions
 * @property Decor $decor
 * @property Decor $decorTo
 */
class Passage extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'passage';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['to_decor_id', 'description', 'image'], 'default', 'value' => null],
            [['passage_type'], 'default', 'value' => 1],
            [['openness'], 'default', 'value' => 2],
            [['is_locked'], 'default', 'value' => 0],
            [['decor_id', 'name'], 'required'],
            [['decor_id', 'to_decor_id', 'passage_type', 'openness', 'is_locked'], 'integer'],
            [['description'], 'string'],
            [['description'], 'filter', 'filter' => [RichTextHelper::class, 'sanitizeMarkdownWithCache']],
            [['name', 'image'], 'string', 'max' => 64],
            [['decor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Decor::class, 'targetAttribute' => ['decor_id' => 'id']],
            [['to_decor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Decor::class, 'targetAttribute' => ['to_decor_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Primary key',
            'decor_id' => 'Foreign key to “decor” table',
            'to_decor_id' => 'Optional foreign key to “decor” table. Identify destination decor',
            'name' => 'Passage',
            'description' => 'Short description',
            'passage_type' => 'Passage type: 1=door,2=gate,3=trapdoor,4=archway,5=portcullis,6=secret_passage,7=portal,8=window,9=hatch',
            'openness' => 'Initial openness state: 0=open,1=ajar,2=closed,3=destroyed',
            'is_locked' => 'Whether the passage starts locked',
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
        return $this->hasMany(Action::class, ['passage_id' => 'id']);
    }

    /**
     * Gets query for [[Decor]].
     *
     * @return \yii\db\ActiveQuery<Decor>
     */
    public function getDecor()
    {
        return $this->hasOne(Decor::class, ['id' => 'decor_id']);
    }

    /**
     * Gets query for [[DecorTo]].
     *
     * @return \yii\db\ActiveQuery<Decor>
     */
    public function getDecorTo()
    {
        return $this->hasOne(Decor::class, ['id' => 'to_decor_id']);
    }
}
