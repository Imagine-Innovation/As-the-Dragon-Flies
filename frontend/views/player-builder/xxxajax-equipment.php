<?php
/** @var yii\web\View $this */
/** @var app/models/Player $player */
/** @var Array $endowments */
/** @var integer $choices */
/** @var Array $items */
/** @var Array $categories */
?>
<h4 class="card-title">Items</h4>
<?php
if ($player && $choices > 0):
    for ($i = 1; $i <= $choices; $i++):
        ?>
        <div id="ajaxItemChoice-<?= $i ?>"></div>
    <?php endfor; ?>
<?php endif; ?>
