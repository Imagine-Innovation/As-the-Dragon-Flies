<?php

namespace common\widgets;

use yii\widgets\InputWidget;
use yii\helpers\Html;

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

        if ($model !== null) {
            $attributeValue = Html::getAttributeValue($model, $attribute);
            $value = is_array($attributeValue) ? '' : (string) $attributeValue;
            $name = (string) Html::getInputName($model, $attribute);
        } else {
            $value = (string) $this->value;
            $name = (string) ($this->name ?? 'RichText');
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
