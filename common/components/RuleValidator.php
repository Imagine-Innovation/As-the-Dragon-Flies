<?php

namespace common\components;

use common\models\Rule;
use yii\validators\Validator;

class RuleValidator extends Validator
{

    public function validateAttribute(mixed $model, mixed $attribute) {
        if (!$model->isValidDefinition()) {
            $model->addError($attribute, $model->errorMessage);
        }
    }
}
