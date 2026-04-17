/**
 * SimpleRichTextEditor Class
 * Manages the frontend behavior for the SimpleRichText widget.
 */
class SimpleRichTextEditor {
    /**
     * Initializes all rich text editors on the page.
     */
    static init() {
        $('.simple-rich-text-editor').each(function() {
            const editor = this;
            const id = editor.id.replace('-editor', '');

            // Avoid double initialization
            if (editor.dataset.initialized) return;

            editor.addEventListener('input', () => SimpleRichTextEditor.updateHidden(id));
            editor.addEventListener('paste', (e) => {
                e.preventDefault();
                const text = (e.clipboardData || window.clipboardData).getData('text/plain');
                document.execCommand('insertText', false, text);
                SimpleRichTextEditor.updateHidden(id);
            });

            editor.dataset.initialized = 'true';
        });
    }

    /**
     * Executes a rich text command.
     * @param {string} id - The ID of the widget.
     * @param {string} cmd - The command to execute.
     */
    static exec(id, cmd) {
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
    }

    /**
     * Updates the hidden input value with Markdown converted from editor HTML.
     * @param {string} id - The ID of the widget.
     */
    static updateHidden(id) {
        const editor = document.getElementById(id + '-editor');
        const hidden = document.getElementById(id);
        if (editor && hidden) {
            hidden.value = this.htmlToMarkdown(editor.innerHTML);
        }
    }

    /**
     * Converts HTML to Markdown.
     * @param {string} html - The HTML string to convert.
     * @returns {string} - The converted Markdown string.
     */
    static htmlToMarkdown(html) {
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
}

// Initialize on DOM ready
$(document).ready(() => {
    SimpleRichTextEditor.init();
});
