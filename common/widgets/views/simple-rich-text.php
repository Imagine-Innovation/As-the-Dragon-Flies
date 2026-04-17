<?php

/** @var string $id */
/** @var string $name */
/** @var string $value */
/** @var string $initialHtml */

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
    ['icon' => 'bi-eraser-fill', 'cmd' => 'p', 'title' => 'Clear format'],
];
?>

<div class="simple-rich-text-container">
    <div class="btn-toolbar mb-2 rounded shadow-sm" role="toolbar" aria-label="Layout toolbar">
        <div class="btn-group me-2" role="group" aria-label="Layout buttons">
            <?php foreach ($buttons as $btn): ?>
                <button type="button" class="btn btn-outline-warning btn-sm"
                        title="<?= $btn['title'] ?>" aria-label="<?= $btn['title'] ?>"
                        onclick="SimpleRichTextEditor.exec('<?= $id ?>', '<?= $btn['cmd'] ?>')">
                    <i class="bi <?= $btn['icon'] ?>"></i>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="<?= $id ?>-editor"
         class="form-control simple-rich-text-editor"
         contenteditable="true"
         style="min-height: 150px; height: auto; overflow-y: auto;">
        <?= $initialHtml ?>
    </div>

    <input type="hidden" id="<?= $id ?>" name="<?= $name ?>" value="<?= htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
</div>
