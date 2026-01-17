<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class ImageUploadForm extends Model
{

    public ?UploadedFile $imageFile;
    public string $folder;

    public function rules() {
        return [
            [['imageFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg', 'checkExtensionByMimeType' => false],
            [['folder'], 'string', 'skipOnEmpty' => false, 'max' => 32],
        ];
    }

    /**
     *
     * @return bool
     */
    public function upload(): bool {
        Yii::debug("*** Debug *** upload this->folder=" . $this->folder, __METHOD__);
        Yii::debug("*** Debug *** upload this->imageFile->baseName=" . $this->imageFile?->baseName, __METHOD__);
        Yii::debug("*** Debug *** upload this->imageFile->extension=" . $this->imageFile?->extension, __METHOD__);
        Yii::debug("*** Debug *** upload this->imageFile->fullPath=" . $this->imageFile?->fullPath, __METHOD__);
        if ($this->imageFile && $this->validate()) {
            $fileName = $this->imageFile->baseName . "." . $this->imageFile->extension;
            $path = $this->uploadPath();
            $fullFileName = $path . DIRECTORY_SEPARATOR . $fileName;
            Yii::debug("*** Debug *** upload fileName={$fileName}", __METHOD__);
            Yii::debug("*** Debug *** upload path={$path}", __METHOD__);
            Yii::debug("*** Debug *** upload fullFileName={$fullFileName}", __METHOD__);
            return $this->imageFile->saveAs($fullFileName, false);
        }
        Yii::debug("*** Debug *** upload validation failed", __METHOD__);
        return false;
    }

    /**
     *
     * @return string
     */
    private function uploadPath(): string {
        $rootPath = 'web' . DIRECTORY_SEPARATOR . 'img';
        if ($this->folder) {
            return $rootPath . DIRECTORY_SEPARATOR . $this->folder;
        }
        return $rootPath;
    }
}
