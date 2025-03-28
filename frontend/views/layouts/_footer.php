<?php

/** @var \yii\web\View $this */
use yii\helpers\Url;
?>
<footer class="footer d-none d-sm-block">
    <ul class="footer__nav">
        <a href="<?= Url::toRoute(['site/index']) ?>">Homepage</a>
        <a href="<?= Url::toRoute(['site/about']) ?>">About</a>
        <a href="#">Support</a>
        <a href="#">News</a>
        <a href="#">Contacts</a>
    </ul>
</footer>
