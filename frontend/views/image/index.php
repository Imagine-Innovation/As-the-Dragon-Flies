<?php

use common\models\RaceGroup;
use frontend\widgets\AjaxContainer;
use frontend\widgets\Button;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'Images';
$this->params['breadcrumbs'][] = $this->title;

$raceGroups = RaceGroup::find()->all();
$initTab = $raceGroups[0]['name'];
$initId = $raceGroups[0]['id'];

$categories = ['Misc', 'Image', 'Item', 'Monster'];
?>
<h3><?= Html::encode($this->title) ?></h3>
<div class="card">
    <div class="card-body">
        <div class="actions">
            <?=
    Button::widget([
        'mode' => 'icon',
        'icon' => 'bi-upload',
        'tooltip' => 'Upload an image',
    ])
?>
        </div>
        <div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                <input type="radio" id="genderM" name="gender" class="custom-control-input" checked
                       onchange='$("#filter").html("M");TableManager.loadGenericAjaxTable(0);'>
                <label class="custom-control-label" for="genderM">Male</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                <input type="radio" id="genderF" name="gender" class="custom-control-input"
                       onchange='$("#filter").html("F");TableManager.loadGenericAjaxTable(0);'>
                <label class="custom-control-label" for="genderF">Female</label>
            </div>
        </div>
        <div class="tab-container">
            <ul class="nav nav-tabs" role="tablist">
                <?php foreach ($raceGroups as $raceGroup): ?>
                    <li class="nav-item">
                        <a class="nav-link<?= $raceGroup->name === $initTab ? ' active' : '' ?>"
                           data-bs-toggle="tab" href="#tab-<?= $raceGroup->name ?>" role="tab"
                           onclick='ImageManager.loadTab("<?= $raceGroup->name ?>", <?= $raceGroup->id ?>);return false;'>
    <?= $raceGroup->name ?>
                        </a>
                    </li>
<?php endforeach; ?>
            </ul>

            <div class="tab-content">
<?php foreach ($raceGroups as $raceGroup): ?>
                    <div class="tab-pane <?= $raceGroup->name === $initTab ? 'active fade show' : 'fade' ?>"
                         id="tab-<?= $raceGroup->name ?>" role="tabpanel">
                    <?= AjaxContainer::widget(['name' => 'ajax-' . $raceGroup->name]) ?>
                    </div>
<?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?=
    $this->renderFile('@app/views/layouts/snippets/ajax-params.php', [
        'route' => 'image/ajax', // default route
        'limit' => 20,
        'initTab' => $initTab,
        'initId' => $initId,
        'filter' => 'M',
    ])
?>

<div class="modal fade" id="imageUploadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Upload a new image</h6>
            </div>
            <div class="modal-body">
                <p class="form-label">Category</p>
<?php foreach ($categories as $category): ?>
                    <div class="custom-control custom-radio custom-control-inline mb-2">
                        <input type="radio" id="uploadRadio-<?= $category ?>" value="<?= $category ?>" name="image-upload-category" class="custom-control-input" <?=
    $category === 'Misc' ? 'checked' : ''
?>>
                        <label class="custom-control-label" for="uploadRadio-<?= $category ?>"><?= $category ?></label>
                    </div>
<?php endforeach; ?>
                <br>
                <br>
                <br>
                <br>
                <form>
                    <label class="btn btn-theme btn--icon-text" for="uploadFileName">
                        <input type="file" id="uploadFileName" name='' class="d-none" accept=".jpg,.gif,.png" required>
                        <i class="bi bi-image"></i> Select an image
                    </label>

                    <div class="error_msg"></div>
                    <div class="uploaded_file_view" id="uploadedFilePreview">
                        <span class="file_remove">X</span>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-theme btn--icon"
                        onclick="ImageManager.upload();">
                    <i class="bi bi-check"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    ImageManager.prepareUpload();
</script>
