<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "step_image".
 *
 * @property int $step_id Foreign key to "step" table
 * @property string $image Image
 * @property string $name Legend (wall, floor, map...)
 * @property string|null $compass_point Indicates the cardinal point of what is represented (N, NE, E, SE, S, SW or W)
 *
 * @property Step $step
 */
class StepImage extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const COMPASS_POINT_N = 'N';
    const COMPASS_POINT_NE = 'NE';
    const COMPASS_POINT_E = 'E';
    const COMPASS_POINT_SE = 'SE';
    const COMPASS_POINT_S = 'S';
    const COMPASS_POINT_SW = 'SW';
    const COMPASS_POINT_W = 'W';

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'step_image';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['compass_point'], 'default', 'value' => null],
            [['step_id', 'image', 'name'], 'required'],
            [['step_id'], 'integer'],
            [['compass_point'], 'string'],
            [['image', 'name'], 'string', 'max' => 32],
            ['compass_point', 'in', 'range' => array_keys(self::optsCompassPoint())],
            [['step_id'], 'unique'],
            [['step_id'], 'exist', 'skipOnError' => true, 'targetClass' => Step::class, 'targetAttribute' => ['step_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'step_id' => 'Foreign key to \"step\" table',
            'image' => 'Image',
            'name' => 'Legend (wall, floor, map...)',
            'compass_point' => 'Indicates the cardinal point of what is represented (N, NE, E, SE, S, SW or W)',
        ];
    }

    /**
     * Gets query for [[Step]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStep() {
        return $this->hasOne(Step::class, ['id' => 'step_id']);
    }


    /**
     * column compass_point ENUM value labels
     * @return string[]
     */
    public static function optsCompassPoint() {
        return [
            self::COMPASS_POINT_N => 'N',
            self::COMPASS_POINT_NE => 'NE',
            self::COMPASS_POINT_E => 'E',
            self::COMPASS_POINT_SE => 'SE',
            self::COMPASS_POINT_S => 'S',
            self::COMPASS_POINT_SW => 'SW',
            self::COMPASS_POINT_W => 'W',
        ];
    }

    /**
     * @return string
     */
    public function displayCompassPoint() {
        return self::optsCompassPoint()[$this->compass_point];
    }

    /**
     * @return bool
     */
    public function isCompassPointN() {
        return $this->compass_point === self::COMPASS_POINT_N;
    }

    public function setCompassPointToN() {
        $this->compass_point = self::COMPASS_POINT_N;
    }

    /**
     * @return bool
     */
    public function isCompassPointNe() {
        return $this->compass_point === self::COMPASS_POINT_NE;
    }

    public function setCompassPointToNe() {
        $this->compass_point = self::COMPASS_POINT_NE;
    }

    /**
     * @return bool
     */
    public function isCompassPointE() {
        return $this->compass_point === self::COMPASS_POINT_E;
    }

    public function setCompassPointToE() {
        $this->compass_point = self::COMPASS_POINT_E;
    }

    /**
     * @return bool
     */
    public function isCompassPointSe() {
        return $this->compass_point === self::COMPASS_POINT_SE;
    }

    public function setCompassPointToSe() {
        $this->compass_point = self::COMPASS_POINT_SE;
    }

    /**
     * @return bool
     */
    public function isCompassPointS() {
        return $this->compass_point === self::COMPASS_POINT_S;
    }

    public function setCompassPointToS() {
        $this->compass_point = self::COMPASS_POINT_S;
    }

    /**
     * @return bool
     */
    public function isCompassPointSw() {
        return $this->compass_point === self::COMPASS_POINT_SW;
    }

    public function setCompassPointToSw() {
        $this->compass_point = self::COMPASS_POINT_SW;
    }

    /**
     * @return bool
     */
    public function isCompassPointW() {
        return $this->compass_point === self::COMPASS_POINT_W;
    }

    public function setCompassPointToW() {
        $this->compass_point = self::COMPASS_POINT_W;
    }
}
