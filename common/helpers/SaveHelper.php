<?php

namespace common\helpers;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

final class SaveHelper
{

    /**
     * @template T of \yii\db\ActiveRecord
     * @param T $model
     * @param bool $throw
     * @return bool
     * @throws \Exception
     */
    public static function save(ActiveRecord &$model, bool $throw = true): bool
    {
        $successfullySaved = $model->save();
        if ($successfullySaved) {
            return true;
        }
        if ($throw) {
            throw new \Exception(implode('<br />', ArrayHelper::getColumn($model->errors, 0, false)));
        }
        return false;
    }
}
