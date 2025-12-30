<?php

namespace frontend\widgets;

use Yii;
use yii\base\Widget;

class AjaxContainer extends Widget
{

    public string $tag = 'div';
    public string $name = 'defaultAjaxContainer';

    /** @var array<string, mixed> $options */
    public array $options = [];

    /**
     *
     * @return string
     */
    public function run(): string {
        Yii::debug("*** AjaxContainer - run() - tag=$this->tag, name=$this->name, param=" . ($this->param ?? "null"));
        return $this->render('ajax-container', [
                    'tag' => $this->tag,
                    'name' => $this->name,
                    'param' => $this->setParam($this->options),
        ]);
    }

    /**
     *
     * @param array<string, mixed> $options
     * @return string
     */
    private function setParam(array $options): string {
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
