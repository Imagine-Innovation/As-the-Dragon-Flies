<?php

namespace frontend\widgets;

use common\helpers\Utilities;
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
        Yii::debug("*** AjaxContainer - run() - tag={$this->tag}, name={$this->name}, param=" . ($this->param ?? "null"));
        return $this->render('ajax-container', [
                    'tag' => $this->tag,
                    'name' => $this->name,
                    'param' => Utilities::formatAttributes($this->options),
        ]);
    }
}
