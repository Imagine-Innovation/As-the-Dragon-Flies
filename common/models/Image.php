<?php

namespace common\models;

use yii\helpers\Url;
use yii\web\UploadedFile;

/**
 * This is the model class for table "image".
 *
 * @property int $id Primary key
 * @property string $file_name File name of the image file with its extension. File path should not be included
 * @property string $category Categorize what is represented in the image: a character, an item, a background, a monster...
 *
 * @property ClassImage[] $classImages
 * @property CharacterClass[] $classes
 * @property Player[] $players
 * @property RaceGroupImage[] $raceGroupImages
 * @property RaceGroup[] $raceGroups
 */
class Image extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'image';
    }

    public ?UploadedFile $image = null;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['file_name', 'category'], 'required'],
            [['file_name', 'category'], 'string', 'max' => 64],
            [['image'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg'],];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Primary key',
            'file_name' => 'File name of the image file with its extension. File path should not be included',
            'category' => 'Categorize what is represented in the image: a character, an item, a background, a monster...',
        ];
    }

    /**
     * Gets query for [[ClassImages]].
     *
     * @return \yii\db\ActiveQuery<ClassImage>
     */
    public function getClassImages()
    {
        return $this->hasMany(ClassImage::class, ['image_id' => 'id']);
    }

    /**
     * Gets query for [[Classes]].
     *
     * @return \yii\db\ActiveQuery<CharacterClass>
     */
    public function getClasses()
    {
        return $this->hasMany(CharacterClass::class, ['id' => 'class_id'])->viaTable('class_image', ['image_id' => 'id']);
    }

    /**
     * Gets query for [[Players]].
     *
     * @return \yii\db\ActiveQuery<Player>
     */
    public function getPlayers()
    {
        return $this->hasMany(Player::class, ['image_id' => 'id']);
    }

    /**
     * Gets query for [[RaceGroupImages]].
     *
     * @return \yii\db\ActiveQuery<RaceGroupImage>
     */
    public function getRaceGroupImages()
    {
        return $this->hasMany(RaceGroupImage::class, ['image_id' => 'id']);
    }

    /**
     * Gets query for [[RaceGroups]].
     *
     * @return \yii\db\ActiveQuery<RaceGroup>
     */
    public function getRaceGroups()
    {
        return $this->hasMany(RaceGroup::class, ['id' => 'race_group_id'])->viaTable('race_group_image', ['image_id' => 'id']);
    }

    /**
     * ******** Custome method ************
     */

    /**
     *
     * @return string
     */
    public function getImageUrl(): string
    {
        $path = 'img/' . $this->category . '/' . $this->file_name;
        return \yii\helpers\Url::to('@web/' . $path);
    }

    /**
     *
     * @return bool
     */
    public function upload(): bool
    {
        if ($this->image === null) {
            return false;
        }
        $fileName = $this->id . "." . $this->image->extension;
        $fullFileName = $this->uploadPath() . $fileName;
        $this->image->saveAs($fullFileName);
        $this->file_name = $fileName;
        $successfullySaved = $this->save();
        if ($successfullySaved) {
            return true;
        }
        throw new \Exception(implode("<br />", ArrayHelper::getColumn($this->errors, 0, false)));
    }

    /**
     *
     * @return string
     */
    private function uploadPath(): string
    {
        $rootPath = Url::to('@web/img/');
        if ($this->category) {
            return $rootPath . $this->category . "/";
        }
        return $rootPath;
    }
}
