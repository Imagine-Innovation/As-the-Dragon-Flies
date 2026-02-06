<?php

/** @var yii\web\View $this */
/** @var string[] $names */
$i = 0;
?>

<h4 class="card-title text-decoration">Name</h4>
<input type="text" class="form-control" id="tmpName"
       onchange='PlayerBuilder.setProperty("name", $("#tmpName").val());'>
<br>
<?php foreach ($names as $name): ?>
    <div class="custom-control custom-radio mb-2">
        <input type="radio" id="name<?= $i ?>" name="name" class="custom-control-input"
               onchange='PlayerBuilder.setProperty("name", "<?= $name ?>");$("#tmpName").val("<?= $name ?>")';'>
        <label class="custom-control-label" for="name<?= $i++ ?>"><?= $name ?></label>
    </div>
<?php endforeach;
