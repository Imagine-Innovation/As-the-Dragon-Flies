<?php
/** @var yii\web\View $this */
/** @var  $properties */
/** @var string $type */
?>
<div class="col-12 col-lg-6 col-xxl-4">
    <article class="card mb-3 h-100" id="Mission<?= $type ?>">
        <div class="card-header">
            <h6 class="card-title"><?= $type ?></h6>
        </div>
        <div class="card-body">
            <?php
            if ($properties) {
                foreach ($properties as $property) {
                    echo "<p>{$property->name}: {$property->description}</p>";
                }
            } else {
                echo "<p>No {$type} defined</p>";
            }
            ?>
        </div>
    </article>
</div>
