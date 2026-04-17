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
        $id = $this->options['id'];
        $value = $this->hasModel() ? Html::getAttributeValue($this->model, $this->attribute) : $this->value;

        $this->registerAssets();

        return $this->renderEditor($id, $value);
    }

    /**
     * Renders the editor UI.
     */
    protected function renderEditor($id, $value)
    {
        $hiddenInput = $this->hasModel() ? Html::activeHiddenInput($this->model, $this->attribute, $this->options) : Html::hiddenInput($this->name, $value, $this->options);

        // $toolbar = $this->renderToolbar($id);
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
     * Renders the toolbar with buttons.
     */
    protected function renderToolbar($id)
    {
        $buttons = [
            ['icon' => 'bi-type-bold', 'cmd' => 'bold', 'title' => 'Bold'],
            ['icon' => 'bi-type-italic', 'cmd' => 'italic', 'title' => 'Italic'],
            ['icon' => 'bi-link-45deg', 'cmd' => 'createLink', 'title' => 'Link'],
            ['icon' => 'bi-list-ul', 'cmd' => 'insertUnorderedList', 'title' => 'Unordered List'],
            ['icon' => 'bi-list-ol', 'cmd' => 'insertOrderedList', 'title' => 'Ordered List'],
            ['icon' => 'bi-type-h1', 'cmd' => 'h1', 'title' => 'Heading 1'],
            ['icon' => 'bi-type-h2', 'cmd' => 'h2', 'title' => 'Heading 2'],
            ['icon' => 'bi-type-h3', 'cmd' => 'h3', 'title' => 'Heading 3'],
            ['icon' => 'bi-type-h4', 'cmd' => 'h4', 'title' => 'Heading 4'],
            ['icon' => 'bi-type-h5', 'cmd' => 'h5', 'title' => 'Heading 5'],
            ['icon' => 'bi-type-h6', 'cmd' => 'h6', 'title' => 'Heading 6'],
        ];

        $html = '<div class="btn-toolbar mb-2 rounded shadow-sm" role="toolbar" aria-label="Layout toolbar">';
        $html .= '<div class="btn-group me-2" role="group" aria-label="Layout buttons">';
        foreach ($buttons as $btn) {
            $html .= Html::button(Html::tag('i', '', ['class' => 'bi ' . $btn['icon']]), [
                'class' => 'btn btn-outline-warning btn-sm',
                'title' => $btn['title'],
                'type' => 'button',
                'onclick' => "SimpleRichTextEditor.exec('{$id}', '{$btn['cmd']}')",
            ]);
        }
        $html .= '</div>';

        $html .= '<div class="btn-group ms-auto" role="group" aria-label="Clear format">';
        $html .= Html::button(Html::tag('i', '', ['class' => 'bi bi-eraser-fill']), [
            'class' => 'btn btn-outline-warning btn-sm',
            'title' => 'Clear Format',
            'type' => 'button',
            'onclick' => "SimpleRichTextEditor.exec('{$id}', 'p')",
        ]);
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Registers the necessary JS and CSS.
     */
    protected function registerAssets()
    {
        $view = $this->getView();
        $id = $this->options['id'];

        $css = "
            .simple-rich-text-editor:focus {
                outline: none;
                border-color: #86b7fe;
                box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            }
            .simple-rich-text-editor h1, .simple-rich-text-editor h2, .simple-rich-text-editor h3,
            .simple-rich-text-editor h4, .simple-rich-text-editor h5, .simple-rich-text-editor h6 {
                margin-top: 0.5rem;
                margin-bottom: 0.5rem;
            }
        ";
        $view->registerCss($css);

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
    public static function sanitize($markdown)
    {
        return RichTextHelper::sanitizeMarkdown($markdown);
    }
}
