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
            [['imageFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg', 'checkExtensionByMimeType' => true],
            [['folder'], 'string', 'skipOnEmpty' => false, 'max' => 32],
        ];
    }

    /**
     *
     * @return bool
     * @throws \yii\base\Exception
     */
    public function upload(): bool
    {
        if ($this->imageFile && $this->validate()) {
            // Sanitize folder input to prevent path traversal
            $safeFolder = preg_replace('/[^a-zA-Z0-9_-]/', '', $this->folder);

            // Generate a unique, random filename
            $fileName = Yii::$app->security->generateRandomString() . '.' . $this->imageFile->extension;

            $path = $this->uploadPath($safeFolder);
            $fullFileName = $path . DIRECTORY_SEPARATOR . $fileName;

            // Ensure the directory exists
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
            return $this->imageFile->saveAs($fullFileName, false);
        }
        return false;
    }

    /**
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
