<?php
/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */
$backgrounds = [
    'var(--blue)', 'var(--light-blue)', 'var(--pink)', 'var(--green)', 'var(--light-green)',
    'var(--bs-yellow)', 'var(--light-yellow)', 'var(--red)', 'var(--cyan)', 'var(--white)',
    'var(--gray-1)', 'var(--gray-2)', 'var(--gray-4)', 'var(--gray-6)', 'var(--gray-8)',
    'var(--gray-a)', 'var(--gray-c)', 'var(--gray-e)', 'var(--background)', 'var(--primary)',
    'var(--secondary)', 'var(--success)', 'var(--info)', 'var(--warning)', 'var(--danger)',
    'var(--light)', 'var(--dark)',
    '#0c5460',
    '#0f6674',
    '#117a8b',
    '#121416',
    '#494f54',
    '#a71d2a',
    '#ba8b00',
    '#cbd3da',
    '#f00',
    'rgba(0,123,255,.25)',
    'rgba(0,123,255,.5)',
    'rgba(108,117,125,.5)',
    'rgba(220,53,69,.25)',
    'rgba(220,53,69,.5)',
    'rgba(23,162,184,.5)',
    'rgba(242,242,242,.2)',
    'rgba(248,249,250,.5)',
    'rgba(250,250,250,.75)',
    'rgba(255,193,7,.5)',
    'rgba(40,167,69,.25)',
    'rgba(40,167,69,.5)',
    'rgba(52,58,64,.5)',
];

$bordercolors = [
    'var(--blue)', 'var(--light-blue)', 'var(--pink)', 'var(--green)', 'var(--light-green)',
    'var(--bs-yellow)', 'var(--light-yellow)', 'var(--red)', 'var(--cyan)', 'var(--white)',
    'var(--gray-1)', 'var(--gray-2)', 'var(--gray-4)', 'var(--gray-6)', 'var(--gray-8)',
    'var(--gray-a)', 'var(--gray-c)', 'var(--gray-e)', 'var(--background)', 'var(--primary)',
    'var(--secondary)', 'var(--success)', 'var(--info)', 'var(--warning)', 'var(--danger)',
    'var(--light)', 'var(--dark)',
    '#0c5460',
    '#0f6674',
    '#117a8b',
    '#121416',
    '#494f54',
    '#a71d2a',
    '#ba8b00',
    '#cbd3da',
    '#f00',
    'rgba(0,123,255,.25)',
    'rgba(0,123,255,.5)',
    'rgba(108,117,125,.5)',
    'rgba(220,53,69,.25)',
    'rgba(220,53,69,.5)',
    'rgba(23,162,184,.5)',
    'rgba(242,242,242,.2)',
    'rgba(248,249,250,.5)',
    'rgba(250,250,250,.75)',
    'rgba(255,193,7,.5)',
    'rgba(40,167,69,.25)',
    'rgba(40,167,69,.5)',
    'rgba(52,58,64,.5)',
];

$colors = [
    'var(--blue)', 'var(--light-blue)', 'var(--pink)', 'var(--green)', 'var(--light-green)',
    'var(--bs-yellow)', 'var(--light-yellow)', 'var(--red)', 'var(--cyan)', 'var(--white)',
    'var(--gray-1)', 'var(--gray-2)', 'var(--gray-4)', 'var(--gray-6)', 'var(--gray-8)',
    'var(--gray-a)', 'var(--gray-c)', 'var(--gray-e)', 'var(--background)', 'var(--primary)',
    'var(--secondary)', 'var(--success)', 'var(--info)', 'var(--warning)', 'var(--danger)',
    'var(--light)', 'var(--dark)',
    '#0c5460',
    '#0f6674',
    '#117a8b',
    '#121416',
    '#494f54',
    '#a71d2a',
    '#ba8b00',
    '#cbd3da',
    '#f00',
    'rgba(0,123,255,.25)',
    'rgba(0,123,255,.5)',
    'rgba(108,117,125,.5)',
    'rgba(220,53,69,.25)',
    'rgba(220,53,69,.5)',
    'rgba(23,162,184,.5)',
    'rgba(242,242,242,.2)',
    'rgba(248,249,250,.5)',
    'rgba(250,250,250,.75)',
    'rgba(255,193,7,.5)',
    'rgba(40,167,69,.25)',
    'rgba(40,167,69,.5)',
    'rgba(52,58,64,.5)',
];

$shadows = [
    'var(--blue)', 'var(--light-blue)', 'var(--pink)', 'var(--green)', 'var(--light-green)',
    'var(--bs-yellow)', 'var(--light-yellow)', 'var(--red)', 'var(--cyan)', 'var(--white)',
    'var(--gray-1)', 'var(--gray-2)', 'var(--gray-4)', 'var(--gray-6)', 'var(--gray-8)',
    'var(--gray-a)', 'var(--gray-c)', 'var(--gray-e)', 'var(--background)', 'var(--primary)',
    'var(--secondary)', 'var(--success)', 'var(--info)', 'var(--warning)', 'var(--danger)',
    'var(--light)', 'var(--dark)',
    '#0c5460',
    '#0f6674',
    '#117a8b',
    '#121416',
    '#494f54',
    '#a71d2a',
    '#ba8b00',
    '#cbd3da',
    '#f00',
    'rgba(0,123,255,.25)',
    'rgba(0,123,255,.5)',
    'rgba(108,117,125,.5)',
    'rgba(220,53,69,.25)',
    'rgba(220,53,69,.5)',
    'rgba(23,162,184,.5)',
    'rgba(242,242,242,.2)',
    'rgba(248,249,250,.5)',
    'rgba(250,250,250,.75)',
    'rgba(255,193,7,.5)',
    'rgba(40,167,69,.25)',
    'rgba(40,167,69,.5)',
    'rgba(52,58,64,.5)',
];
?>
<h1>Background colors</h1>
<div class="container-fluid">
    <div class="row g-1">
        <?php foreach ($backgrounds as $background): ?>
            <div class="col-2 col-lg-1">
                <div class="card h-100" style="background-color: <?= $background ?>;">
                    <div class="card-body" style="padding: 5px;">
                        <p style="color: black;"><?= $background ?></p>
                        <p style="color: white;"><?= $background ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<br>
<br>
<h1>Border colors</h1>
<div class="container-fluid">
    <div class="row g-1">
        <?php foreach ($bordercolors as $bordercolor): ?>
            <div class="col-2 col-lg-1">
                <div class="card h-100" style="border: 5px solid <?= $bordercolor ?>;">
                    <div class="card-body" style="padding: 5px;">
                        <p style="color: white;"><?= $bordercolor ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<br>
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
<br>
<h1>Shodows</h1>
<div class="container-fluid">
    <div class="row g-1">
        <?php foreach ($shadows as $shadow): ?>
            <div class="col-2 col-lg-1">
                <div class="card h-100" style="box-shadow: <?= $shadow ?>;">
                    <div class="card-body" style="padding: 5px;">
                        <p><?= $shadow ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
