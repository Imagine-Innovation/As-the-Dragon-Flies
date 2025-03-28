<?php

namespace frontend\widgets;

use Yii;
use yii\base\Widget;

class AjaxContainer extends Widget {

    public $name;
    public $options;

    public function run() {
        Yii::debug("*** AjaxContainer - run() - name=$this->name, param=" . ($this->param ?? "null"));
        return $this->render('ajax-container', [
                    'name' => $this->name,
                    'param' => $this->setParam($this->options),
        ]);
    }

    private function setParam($options) {
        $param = "";
        if ($options) {
            $keys = array_keys($options);
            $values = array_values($options);
            $params = [];

            for ($i = 0; $i < count($options); $i++) {
                $params[] = $keys[$i] . '="' . $values[$i] . '"';
            }
            $param = implode(" ", $params);
        }

        return $param;
    }
}
