<?php

namespace frontend\widgets;

use yii\base\Widget;

class ModalDesc extends Widget {

    public $name;
    public $description;
    public $maxLength;
    public $type;
    public $id;

    public function run() {
        if (!$this->description) {
            return "";
        }

        $this->maxLength = $this->maxLength ?? 250;
        if (mb_strlen($this->description) <= $this->maxLength) {
            return $this->render('modal-desc-raw', [
                        'description' => $this->description,
            ]);
        }

        return $this->render('modal-desc', [
                    'UUID' => $this->type . $this->id,
                    'description' => $this->description,
                    'maxLength' => $this->maxLength,
                    'name' => $this->name,
        ]);
    }
}
