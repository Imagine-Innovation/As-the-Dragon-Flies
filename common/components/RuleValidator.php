<?php

namespace common\components;

use common\models\Rule;
use yii\validators\Validator;

class RuleValidator extends Validator
{
    /**
     * @param mixed $model
     * @param mixed $attribute
     * @return void
     */
    public function validateAttribute(mixed $model, mixed $attribute): void
    {
        if (!$model instanceof Rule || !is_string($attribute)) {
            return;
        }

        if (!$model->isValidDefinition()) {
            $model->addError($attribute, $model->errorMessage);
        }
    }
}
