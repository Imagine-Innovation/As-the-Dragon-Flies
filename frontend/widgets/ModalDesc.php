<?php

namespace frontend\widgets;

use common\helpers\Utilities;
use yii\base\Widget;

class ModalDesc extends Widget
{
    const MAX_LENGTH = 250;

    public string $name;
    public ?string $description = null;
    public ?int $maxLength = null;
    public ?string $type = null;
    public ?int $id = null;

    public function run()
    {
        if (!$this->description) {
            return '';
        }

        $maxLength = $this->maxLength ?? self::MAX_LENGTH;

        if (mb_strlen($this->description) <= $maxLength) {
            return $this->render('modal-desc-raw', [
                'description' => $this->description,
            ]);
        }

        $id = $this->id ?? Utilities::newUUID();

        return $this->render('modal-desc', [
            'UUID' => ($this->type ?? '') . $id,
            'description' => $this->description,
            'maxLength' => $maxLength,
            'name' => $this->name,
        ]);
    }
}
