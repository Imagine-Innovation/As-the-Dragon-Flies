<?php

namespace common\models;

use common\helpers\RichTextHelper;
use Yii;

/**
 * This is the model class for table "menu".
 *
 * @property int $access_right_id Foreign key to “access_right” table
 * @property string $label Label
 * @property string $icon icon
 * @property string $tooltip Tooltip
 * @property string|null $card_title Card title
 * @property string|null $subtitle Subtitle
 * @property string|null $description Card menu description
 * @property string|null $button_label Button label
 * @property string|null $image Image
 * @property int $is_context The image is context dependent
 * @property int $sort_order Sort order
 *
 * @property AccessRight $accessRight
 */
class Menu extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'menu';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['card_title', 'subtitle', 'description', 'button_label', 'image'], 'default', 'value' => null],
            [['is_context'], 'default', 'value' => 0],
            [['sort_order'], 'default', 'value' => 1000],
            [['access_right_id', 'label', 'icon', 'tooltip'], 'required'],
            [['access_right_id', 'is_context', 'sort_order'], 'integer'],
            [['description'], 'string'],
            [['description'], 'filter', 'filter' => [RichTextHelper::class, 'sanitizeWithCache']],
            [['label', 'icon', 'image'], 'string', 'max' => 64],
            [['tooltip', 'card_title', 'subtitle', 'button_label'], 'string', 'max' => 255],
            [['access_right_id'], 'unique'],
            [['access_right_id'], 'exist', 'skipOnError' => true, 'targetClass' => AccessRight::class, 'targetAttribute' => ['access_right_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'access_right_id' => 'Foreign key to “access_right” table',
            'label' => 'Label',
            'icon' => 'icon',
            'tooltip' => 'Tooltip',
            'card_title' => 'Card title',
            'subtitle' => 'Subtitle',
            'description' => 'Card menu description',
            'button_label' => 'Button label',
            'image' => 'Image',
            'is_context' => 'The image is context dependent',
            'sort_order' => 'Sort order',
        ];
    }

    /**
     * Gets query for [[AccessRight]].
     *
     * @return \yii\db\ActiveQuery<AccessRight>
     */
    public function getAccessRight()
    {
        return $this->hasOne(AccessRight::class, ['id' => 'access_right_id']);
    }
}
