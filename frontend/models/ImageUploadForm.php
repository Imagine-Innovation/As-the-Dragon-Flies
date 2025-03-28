<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class ImageUploadForm extends Model {

    /**
     * @var UploadedFile
     */
    public $imageFile;
    public $folder;

    public function rules() {
        return [
            [['imageFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg', 'checkExtensionByMimeType' => false],
            [['folder'], 'string', 'skipOnEmpty' => false, 'max' => 32],
        ];
    }

    public function upload() {
        Yii::debug("*** Debug *** upload this->folder=" . $this->folder, __METHOD__);
        Yii::debug("*** Debug *** upload this->imageFile->baseName=" . $this->imageFile->baseName, __METHOD__);
        Yii::debug("*** Debug *** upload this->imageFile->extension=" . $this->imageFile->extension, __METHOD__);
        Yii::debug("*** Debug *** upload this->imageFile->fullPath=" . $this->imageFile->fullPath, __METHOD__);
        if ($this->validate()) {
            $fileName = $this->imageFile->baseName . "." . $this->imageFile->extension;
            $path = $this->uploadPath();
            $fullFileName = $path . DIRECTORY_SEPARATOR . $fileName;
            Yii::debug("*** Debug *** upload fileName=$fileName", __METHOD__);
            Yii::debug("*** Debug *** upload path=$path", __METHOD__);
            Yii::debug("*** Debug *** upload fullFileName=$fullFileName", __METHOD__);
            return $this->imageFile->saveAs($fullFileName, false);
        }
        Yii::debug("*** Debug *** upload validation failed", __METHOD__);
        return false;
    }

    private function uploadPath() {
        $rootPath = 'web' . DIRECTORY_SEPARATOR . 'img';
        if ($this->folder) {
            return $rootPath . DIRECTORY_SEPARATOR . $this->folder;
        }
        return $rootPath;
    }
}
