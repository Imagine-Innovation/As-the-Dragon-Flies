<?php
/** @var yii\web\View $this */
/** @var common\models\ItemCategory[] $models */
/** @var number $choice */
/** @var string $alreadySelectedItems */
/** @var string $quantity */
?>
<h4><?= $models[0]->category->name ?></h4>
<div class="container-fluid">
    <div class="row g-4">
        <?php
        foreach ($models as $itemCategory):
            $item = $itemCategory->item;
            ?>
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <div class="card h-100">
                    <?php if ($item->image): ?>
                        <img class="card-img-top" src="img/item/<?= $item->image->file_name ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <div class="custom-control custom-radio mb-2">
                            <input type="radio" class="custom-control-input"
                                   id="selectItemRadio-<?= $item->id ?>" name="initialEquipmentRadio"
                                   value="<?= $choice ?>,<?= $item->id ?>|<?= $quantity ?><?= $alreadySelectedItems ? ",$alreadySelectedItems" : '' ?>">
                            <label class="custom-control-label" for="selectItemRadio-<?= $item->id ?>"><?= $item->name ?></label>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
