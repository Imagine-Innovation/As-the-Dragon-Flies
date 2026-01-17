<?php

namespace frontend\widgets;

use yii\base\Widget;

class ModalDesc extends Widget
{

    public string $name;
    public string $description;
    public int $maxLength;
    public ?string $type = null;
    public int $id;

    public function run() {
        if (!$this->description) {
            return '';
        }

        $this->maxLength = $this->maxLength ?? 250;
        if (mb_strlen($this->description) <= $this->maxLength) {
            return $this->render('modal-desc-raw', [
                        'description' => $this->description,
            ]);
        }

        return $this->render('modal-desc', [
                    'UUID' => ($this->type ?? '') . $this->id,
                    'description' => $this->description,
                    'maxLength' => $this->maxLength,
                    'name' => $this->name,
        ]);
    }
}
