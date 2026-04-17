<?php

namespace common\widgets;

use common\helpers\RichTextHelper;
use yii\helpers\Html;
use yii\widgets\InputWidget;

/**
 * SimpleRichText widget is a minimal rich text editor that inputs and stores Markdown.
 *
 * Supported features: Bold, Italic, Link, Unordered List, Ordered List, Headings (H1-H6).
 */
class SimpleRichText extends InputWidget
{

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        /** @var string $id */
        $id = $this->options['id'];

        $model = $this->model;
        /** @var \yii\base\Model|null $model */
        if ($model === null) {
            return '';
        }
        $value = $this->hasModel() ? Html::getAttributeValue($model, $this->attribute ?? '') : $this->value;

        $this->registerAssets();

        Yii::debug("*** run SimpleRichText Widget error " . print_r(gettype($value), true));
        if (!is_string($value)) {
            /** */
            return '';
        }

        return $this->renderEditor($id, $value);
    }

    /**
     * Renders the editor UI.
     */

    /**
     *
     * @param string $id
     * @param string $value
     * @return string
     */
    protected function renderEditor(string $id, string $value): string
    {
        $model = $this->model;
        /** @var \yii\base\Model|null $model */
        if ($model === null) {
            return '';
        }

        $hiddenInput = $this->hasModel() ? Html::activeHiddenInput($model, $this->attribute ?? '', $this->options) : Html::hiddenInput($this->name ?? 'RichText', $value, $this->options);

        $toolbar = $this->render('simple-rich-text-toolbar', [
            'id' => $id,
        ]);

        // Use the existing MarkDown widget to render initial HTML from Markdown value
        $initialHtml = MarkDown::widget(['content' => $value]);

        $editor = Html::tag('div', $initialHtml, [
            'id' => $id . '-editor',
            'class' => 'form-control simple-rich-text-editor',
            'contenteditable' => 'true',
            'style' => 'min-height: 150px; height: auto; overflow-y: auto;',
        ]);

        return Html::tag('div', $toolbar . $editor . $hiddenInput, [
                    'class' => 'simple-rich-text-container',
        ]);
    }

    /**
     * Registers the necessary JS and CSS.
     *
     * @return void
     */
    protected function registerAssets(): void
    {
        $view = $this->getView();
        $id = $this->options['id'];

        $mainJs = <<<'JS'
        var SimpleRichTextEditor = {
            exec: function(id, cmd) {
                const editor = document.getElementById(id + '-editor');
                if (!editor) return;
                editor.focus();

                if (cmd.match(/^(h[1-6]|p)$/)) {
                    document.execCommand('formatBlock', false, cmd.toUpperCase());
                } else if (cmd === 'createLink') {
                    const url = prompt('Enter the URL:');
                    if (url) {
                        document.execCommand(cmd, false, url);
                    }
                } else {
                    document.execCommand(cmd, false, null);
                }
                this.updateHidden(id);
            },
            updateHidden: function(id) {
                const editor = document.getElementById(id + '-editor');
                const hidden = document.getElementById(id);
                if (editor && hidden) {
                    hidden.value = this.htmlToMarkdown(editor.innerHTML);
                }
            },
            htmlToMarkdown: function(html) {
                const temp = document.createElement('div');
                temp.innerHTML = html;

                const walk = (node) => {
                    let text = '';
                    node.childNodes.forEach(child => {
                        if (child.nodeType === 3) {
                            text += child.textContent;
                        } else if (child.nodeType === 1) {
                            const tagName = child.tagName.toLowerCase();
                            switch(tagName) {
                                case 'b':
                                case 'strong':
                                    text += '**' + walk(child) + '**';
                                    break;
                                case 'i':
                                case 'em':
                                    text += '*' + walk(child) + '*';
                                    break;
                                case 'a':
                                        let href = child.getAttribute('href') || '';
                                        // Simple protocol validation
                                        const isSafe = /^(https?:\/\/|mailto:|tel:|\/\/|\/|\.\.?\/|#)/i.test(href) || /^[^:]+?(\/|$)/.test(href);
                                        if (!isSafe) href = '#';
                                    text += '[' + walk(child) + '](' + href + ')';
                                    break;
                                case 'ul':
                                    text += '\n\n' + walk(child) + '\n';
                                    break;
                                case 'ol':
                                    text += '\n\n' + walk(child) + '\n';
                                    break;
                                case 'li':
                                    const parent = child.parentNode;
                                    const isOrdered = parent && parent.tagName.toLowerCase() === 'ol';
                                    const prefix = isOrdered ? '1. ' : '* ';
                                    text += prefix + walk(child) + '\n';
                                    break;
                                case 'h1': text += '\n# ' + walk(child) + '\n'; break;
                                case 'h2': text += '\n## ' + walk(child) + '\n'; break;
                                case 'h3': text += '\n### ' + walk(child) + '\n'; break;
                                case 'h4': text += '\n#### ' + walk(child) + '\n'; break;
                                case 'h5': text += '\n##### ' + walk(child) + '\n'; break;
                                case 'h6': text += '\n###### ' + walk(child) + '\n'; break;
                                case 'p':
                                case 'div':
                                    text += '\n' + walk(child) + '\n';
                                    break;
                                case 'br':
                                    text += '\n';
                                    break;
                                default:
                                    text += walk(child);
                            }
                        }
                    });
                    return text;
                };

                let md = walk(temp);
                md = md.replace(/\n\s*\n\s*\n/g, '\n\n');
                return md.trim();
            }
        };
JS;
        $view->registerJs($mainJs, \yii\web\View::POS_END, 'simple-rich-text-main');

        $initJs = "
            (function() {
                const id = '{$id}';
                const editor = document.getElementById(id + '-editor');
                if (editor) {
                    editor.addEventListener('input', () => SimpleRichTextEditor.updateHidden(id));
                    editor.addEventListener('paste', (e) => {
                        e.preventDefault();
                        const text = (e.clipboardData || window.clipboardData).getData('text/plain');
                        document.execCommand('insertText', false, text);
                        SimpleRichTextEditor.updateHidden(id);
                    });
                }
            })();
        ";
        $view->registerJs($initJs);
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
        return RichTextHelper::sanitizeMarkdown($markdown);
    }
}
