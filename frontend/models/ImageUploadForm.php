<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class ImageUploadForm extends Model
{
    public ?UploadedFile $imageFile;
    public string $folder;

    public function rules()
    {
        return [
            [
                ['imageFile'],
                'file',
                'skipOnEmpty' => false,
                'extensions' => 'png, jpg',
                'checkExtensionByMimeType' => true,
            ],
            [['folder'], 'string', 'skipOnEmpty' => false, 'max' => 32],
        ];
    }

    /**
     *
     * @return bool
     */
    public function upload(): bool
    {
        Yii::debug('*** Debug *** upload this->folder=' . $this->folder, __METHOD__);
        Yii::debug('*** Debug *** upload this->imageFile->baseName=' . $this->imageFile?->baseName, __METHOD__);
        Yii::debug('*** Debug *** upload this->imageFile->extension=' . $this->imageFile?->extension, __METHOD__);
        Yii::debug('*** Debug *** upload this->imageFile->fullPath=' . $this->imageFile?->fullPath, __METHOD__);
        if ($this->imageFile && $this->validate()) {
            // Sanitize folder input to prevent path traversal
            $safeFolder = preg_replace('/[^a-zA-Z0-9_-]/', '', $this->folder);

            // Generate a unique, random filename
            $fileName = Yii::$app->security->generateRandomString() . '.' . $this->imageFile->extension;

            $path = $this->uploadPath($safeFolder);
            // Ensure the directory exists
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }

            $fullFileName = $path . DIRECTORY_SEPARATOR . $fileName;
            Yii::debug("*** Debug *** upload fileName={$fileName}", __METHOD__);
            Yii::debug("*** Debug *** upload path={$path}", __METHOD__);
            Yii::debug("*** Debug *** upload fullFileName={$fullFileName}", __METHOD__);

            return $this->imageFile->saveAs($fullFileName, false);
        }
        Yii::debug('*** Debug *** upload validation failed', __METHOD__);
        return false;
    }

    /**
     *
     * @param string|null $folder
     * @return string
     */
    private function uploadPath(?string $folder = null): string
    {
        $rootPath = 'web' . DIRECTORY_SEPARATOR . 'img';
        if ($folder) {
            return $rootPath . DIRECTORY_SEPARATOR . $folder;
        }
        return $rootPath;
    }
}
