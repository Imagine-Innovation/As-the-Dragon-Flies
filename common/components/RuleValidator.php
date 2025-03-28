<?php
namespace common\components;

use yii\validators\Validator;

class RuleValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        if (!$model->isValidDefinition()) {
            $model->addError($attribute, $model->errorMessage);
        }
    }
}
