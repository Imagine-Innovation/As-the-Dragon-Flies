<?php

use frontend\widgets\ItemTable;

/** @var yii\web\View $this */
?>
<div class="table-responsive">
    <table class="table table-dark table-hover mb-0">
        <thead>
            <tr>
                <?= ItemTable::renderTableHeader() ?>
            </tr>
        </thead>
        <tbody>
            <?= ItemTable::renderTableBody() ?>
        </tbody>
    </table>
</div>
