<?php

namespace common\widgets;

use yii\widgets\InputWidget;
use yii\base\Model;

/**
 * SimpleRichText widget is a minimal rich text editor that inputs and stores Markdown.
 *
 * Supported features: Bold, Italic, Link, Unordered List, Ordered List, Headings (H1-H6).
 */
class SimpleRichText extends InputWidget
{

    /**
     * {@inheritdoc}
     * @return string
     */
    public function run(): string
    {
        $id = (string) ($this->options['id'] ?? $this->getId());
        $model = $this->model;
        $attribute = (string) $this->attribute;

        if ($model instanceof Model) {
            $value = (string) $model->$attribute;
            $name = $this->getInputName($model, $attribute);
        } else {
            $value = (string) $this->value;
            $name = (string) $this->name;
        }

        $initialHtml = MarkDown::widget(['content' => $value]);

        return $this->render('simple-rich-text', [
                    'id' => $id,
                    'name' => $name,
                    'value' => $value,
                    'initialHtml' => $initialHtml,
        ]);
    }

    /**
     * Helper to get the input name for a model and attribute.
     * @param Model $model
     * @param string $attribute
     * @return string
     */
    protected function getInputName(Model $model, string $attribute): string
    {
        $formName = $model->formName();
        if ($formName === '') {
            return $attribute;
        }
        return $formName . '[' . $attribute . ']';
    }

    /**
     * Server-side sanitization of the markdown.
     * This can be used in the model rules.
     *
     * @param string|null $markdown
     * @return string
     */
    public static function sanitize(?string $markdown): string
    {
        return \common\helpers\RichTextHelper::sanitizeMarkdown($markdown);
    }
}
