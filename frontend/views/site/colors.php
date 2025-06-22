<?php
/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */

$colors = [
    'var(--blue)',
    'var(--blue-25)',
    'var(--blue-50)',
    'var(--light-blue)',
    'var(--cyan)',
    'var(--cyan-50)',
    'var(--teal)',
    'var(--light-green)',
    'var(--green)',
    'var(--green-50)',
    'var(--green-25)',
    'var(--indigo)',
    'var(--purple)',
    'var(--red)',
    'var(--red-25)',
    'var(--red-50)',
    'var(--orange)',
    'var(--yellow)',
    'var(--yellow-50)',
    'var(--light-yellow)',
    'var(--pink)',
    'var(--black)',
    'var(--dark-95)',
    'var(--dark-75)',
    'var(--dark-50)',
    'var(--dark-25)',
    'var(--dark-10)',
    'var(--dark-00)',
    'var(--dark-gray)',
    'var(--dark-gray-50)',
    'var(--gray)',
    'var(--gray-50)',
    'var(--gray-1)',
    'var(--gray-2)',
    'var(--gray-4)',
    'var(--gray-6)',
    'var(--gray-8)',
    'var(--gray-a)',
    'var(--gray-c)',
    'var(--gray-e)',
    'var(--light-05)',
    'var(--light-10)',
    'var(--light-25)',
    'var(--light-50)',
    'var(--light-75)',
    'var(--light-85)',
    'var(--light-gray-50)',
    'var(--light-gray)',
    'var(--white)',
];
?>
<h1>Background colors</h1>
<div class="container-fluid">
    <div class="row g-1">
        <?php foreach ($colors as $color): ?>
            <div class="col-2 col-lg-1">
                <div class="card h-100" style="background-color: <?= $color ?>;">
                    <div class="card-body" style="padding: 5px;">
                        <p style="color: black;"><?= $color ?></p>
                        <p style="color: white;"><?= $color ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<br>
<hr>
<br>
<div class="container-fluid" style="background-color: white;">
    <div class="row g-1">
        <?php foreach ($colors as $color): ?>
            <div class="col-2 col-lg-1">
                <div class="card h-100" style="background-color: <?= $color ?>;">
                    <div class="card-body" style="padding: 5px;">
                        <p style="color: black;"><?= $color ?></p>
                        <p style="color: white;"><?= $color ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<br>
<hr>
<br>
<h1>Border colors</h1>
<div class="container-fluid">
    <div class="row g-1">
        <?php foreach ($colors as $color): ?>
            <div class="col-2 col-lg-1">
                <div class="card h-100" style="border: 5px solid <?= $color ?>;">
                    <div class="card-body" style="padding: 5px;">
                        <p style="color: white;"><?= $color ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<br>
<hr>
<br>
<h1>Colors</h1>
<div class="container-fluid">
    <div class="row g-1">
        <?php foreach ($colors as $color): ?>
            <div class="col-2 col-lg-1">
                <div class="card h-100">
                    <div class="card-body" style="padding: 5px;">
                        <p style="color: <?= $color ?>;"><?= $color ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<div class="container-fluid">
    <div class="row g-1">
        <?php foreach ($colors as $color): ?>
            <div class="col-2 col-lg-1">
                <div class="card h-100" style="background-color: rgba(255,255,255,.75);">
                    <div class="card-body" style="padding: 5px;">
                        <p style="color: <?= $color ?>;"><?= $color ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<br>
<hr>
<br>
<h1>Shadows</h1>
<div class="container-fluid">
    <div class="row g-1">
        <?php foreach ($colors as $color): ?>
            <div class="col-2 col-lg-1">
                <div class="card h-100" style="box-shadow: <?= $color ?>;">
                    <div class="card-body" style="padding: 5px;">
                        <p><?= $color ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
