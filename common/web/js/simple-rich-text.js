/**
 * SimpleRichTextEditor Class
 * Manages the frontend behavior for the SimpleRichText widget.
 */
class SimpleRichTextEditor {
    /**
     * Initializes event delegation for rich text editors.
     * Using delegation to support elements injected via AJAX/PJAX.
     */
    static init() {
        if (this._initialized) return;

        $(document).on('input', '.simple-rich-text-editor', (e) => {
            const editor = e.currentTarget;
            const id = editor.id.replace('-editor', '');
            this.updateHidden(id);
        });

        $(document).on('paste', '.simple-rich-text-editor', (e) => {
            e.preventDefault();
            const text = (e.originalEvent.clipboardData || window.clipboardData).getData('text/plain');
            document.execCommand('insertText', false, text);

            const editor = e.currentTarget;
            const id = editor.id.replace('-editor', '');
            this.updateHidden(id);
        });

        this._initialized = true;
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
        } else if (cmd === 'insertHorizontalRule') {
            document.execCommand('insertHorizontalRule', false, null);
        } else if (cmd === 'createLink') {
            const url = prompt('Enter the URL:');
            if (url) {
                document.execCommand(cmd, false, url);
            }
        } else if (cmd === 'clear') {
            document.execCommand('removeFormat', false, null);
            document.execCommand('unlink', false, null);
            document.execCommand('formatBlock', false, 'P');
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
     * Sanitizes a URL for use in a Markdown link.
     * @param {string|null} href
     * @returns {string}
     */
    static sanitizeHref(href) {
        href = href || '';
        const isSafe = /^(https?:\/\/|mailto:|tel:|\/\/|\/|\.\.?\/|#)/i.test(href) || /^[^:]+?(\/|$)/.test(href);
        return isSafe ? href : '#';
    }

    /**
     * Gets the Markdown prefix for a heading tag.
     * @param {string} tagName
     * @returns {string}
     */
    static headingPrefix(tagName) {
        const level = parseInt(tagName[1], 10);
        return '#'.repeat(level);
    }

    /**
     * Checks if a tag is a block-level element.
     * @param {string} tagName
     * @returns {boolean}
     */
    static isBlock(tagName) {
        return tagName === 'p' || tagName === 'div';
    }

    /**
     * Checks if a tag is a list element.
     * @param {string} tagName
     * @returns {boolean}
     */
    static isList(tagName) {
        return tagName === 'ul' || tagName === 'ol';
    }

    /**
     * Converts HTML to Markdown.
     * @param {string} html - The HTML string to convert.
     * @returns {string} - The converted Markdown string.
     */
    static htmlToMarkdown(html) {
        // Use DOMParser instead of innerHTML for better security
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const root = doc.body;

        const walk = (node) => {
            const parts = [];

            const wrapWithMarkdownDelimitersPreservingWhitespace = (child, delimiterStart, delimiterEnd) => {
                const content = walk(child);
                const leadingSpace = content.match(/^\s*/)[0];
                const trailingSpace = content.match(/\s*$/)[0];
                const trimmed = content.trim();

                if (trimmed) {
                    parts.push(leadingSpace, delimiterStart, trimmed, delimiterEnd, trailingSpace);
                } else {
                    parts.push(content);
                }
            };

            node.childNodes.forEach(child => {
                if (child.nodeType === 3) {
                    parts.push(child.textContent);
                } else if (child.nodeType === 1) {
                    const tagName = child.tagName.toLowerCase();
                    switch(tagName) {
                        case 'b':
                        case 'strong': {
                            wrapWithMarkdownDelimitersPreservingWhitespace(child, '**', '**');
                            break;
                        }
                        case 'i':
                        case 'em': {
                            wrapWithMarkdownDelimitersPreservingWhitespace(child, '*', '*');
                            break;
                        }
                        case 'a': {
                            const href = this.sanitizeHref(child.getAttribute('href'));
                            wrapWithMarkdownDelimitersPreservingWhitespace(child, '[', '](' + href + ')');
                            break;
                        }
                        case 'li': {
                            const parent = child.parentNode;
                            const isOrdered = parent && parent.tagName.toLowerCase() === 'ol';
                            const prefix = isOrdered ? '1. ' : '* ';
                            parts.push(prefix, walk(child), '\n');
                            break;
                        }
                        case 'br':
                            parts.push('\n');
                            break;
                        case 'hr':
                            parts.push('\n---\n');
                            break;
                        default:
                            if (this.isList(tagName)) {
                                parts.push('\n\n', walk(child), '\n');
                            } else if (/^h[1-6]$/.test(tagName)) {
                                parts.push('\n', this.headingPrefix(tagName), ' ', walk(child), '\n');
                            } else if (this.isBlock(tagName)) {
                                parts.push('\n', walk(child), '\n');
                            } else {
                                parts.push(walk(child));
                            }
                    }
                }
            });
            return parts.join('');
        };

        let md = walk(root);
        md = md.replace(/\n\s*\n\s*\n/g, '\n\n');
        return md.trim();
    }
}

// Initialize on DOM ready
$(document).ready(() => {
    SimpleRichTextEditor.init();
});
