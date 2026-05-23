<?php

/** @var \yii\web\View $this */
use yii\helpers\Url;

?>
<footer class="footer d-none d-sm-block">
    <ul class="footer__nav">
        <a href="<?= Url::toRoute(['site/index']) ?>"><?= Yii::t('app', 'Homepage') ?></a>
        <a href="<?= Url::toRoute(['site/about']) ?>"><?= Yii::t('app', 'About') ?></a>
        <a href="#"><?= Yii::t('app', 'Support') ?></a>
        <a href="#"><?= Yii::t('app', 'News') ?></a>
        <a href="#"><?= Yii::t('app', 'Contacts') ?></a>
    </ul>
</footer>
