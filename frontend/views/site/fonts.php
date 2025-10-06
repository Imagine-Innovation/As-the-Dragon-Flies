<?php
/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */
$fonts = [
    /* handwritten */
    'cursiveserif', 'senjasantuy',
    /* layout */
    'balgruf', 'berenika', 'cavalier', 'charakterny', 'dumbledor2', 'dumbledor2thin',
    'gamestation', 'goudymedieval', 'ipanemasecco', 'lombardic', 'lombardicnarrow',
    'medievalsharp', 'romanuncialmodern', 'uncialantiqua',
    /* parchment */
    'acharmingfont', 'acharmingfontexpanded', 'alpineregular', 'bastarda', 'buoscardiggs', 'buwicked', 'cardinal',
    'catwalzhari', 'cloisterblacklight', 'colchesterblack', 'devinneswash',
    'fetteclassicunzfraktur', 'hildasonnenschein', 'insula', 'lancaster', 'marigoldwild',
    'mediaevalcaps', 'oldeenglishregular', 'primitive', 'rotundapommerania', 'sidhenoble',
    /* special */
    'cirnajacalligraphy', 'elficcaslin', 'glagolitsa', 'greifswaldertengwar',
    'saratieldamarltr', 'tengwareldamar', 'tengwarparmaite', 'valmariceldamar',
];

$sampleText = "As the Dragon Flies";
?>
<link href="/frontend/web/css/dev-fonts.css" rel="stylesheet">
<div class="container-fluid">
    <div class="row g-4">
        <div class="col-6 col-xxl-3">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Normal</h4>
                    <h1 class="display-1"><?= $sampleText ?></h1>
                    <p class="h1"><?= $sampleText ?></p>
                    <p class="h2"><?= $sampleText ?></p>
                    <p class="h3"><?= $sampleText ?></p>
                    <p class="h4"><?= $sampleText ?></p>
                    <p class="h4"><?= $sampleText ?></p>
                    <p class="fw-bold"><?= $sampleText ?></p>
                    <p class="fw-bolder"><?= $sampleText ?></p>
                    <p class="fw-semibold"><?= $sampleText ?>.</p>
                    <p class="fw-medium"><?= $sampleText ?></p>
                    <p class="fw-normal"><?= $sampleText ?></p>
                    <p class="fw-light"><?= $sampleText ?></p>
                    <p class="fw-lighter"><?= $sampleText ?></p>
                    <p class="fst-italic"><?= $sampleText ?></p>
                    <p class="fst-normal"><?= $sampleText ?></p>
                    <p class="lead">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                        Vivamus massa sapien, maximus eget bibendum id, dignissim cursus quam.
                        Nullam pharetra eget arcu eget interdum. Orci varius natoque penatibus
                        et magnis dis parturient montes, nascetur ridiculus mus. Quisque sit
                        amet ligula efficitur, aliquet sapien ut, porttitor mi. Aenean euismod
                        urna eget metus efficitur mattis. Pellentesque nisl erat, consequat eu
                        consequat quis, porttitor sed neque. Etiam sodales sapien vitae sodales
                        scelerisque. Aenean sit amet tempor neque. Donec commodo lorem felis,
                        ut mollis felis posuere volutpat.
                    </p>
                    <p>
                        Sed odio enim, mollis ac nulla vel, elementum sodales urna. Sed
                        tristique pretium leo blandit malesuada. Nulla eget orci eu enim
                        venenatis facilisis. Orci varius natoque penatibus et magnis dis
                        parturient montes, nascetur ridiculus mus. Donec vel turpis pretium,
                        sagittis ligula ullamcorper, aliquet arcu. Ut massa est, convallis ac
                        nibh sit amet, tempus rutrum odio. Nullam eget urna ac orci fermentum
                        venenatis vel et lacus. Duis at urna sagittis, fringilla lorem at,
                        rhoncus urna. Sed non odio et libero maximus vehicula non quis tortor.
                        Duis porta urna vel fringilla sodales. Donec a felis in arcu scelerisque
                        pretium. Maecenas eu commodo nisl. Duis elit odio, rutrum vitae sem et,
                        maximus mollis justo. Duis augue augue, pulvinar vel dui et, imperdiet
                        scelerisque urna.
                    </p>
                </div>
            </div>
        </div>
        <?php foreach ($fonts as $font): ?>
            <div class="col-6 col-xxl-3">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title"><?= $font ?></h4>
                        <h1 class="display-1 text-<?= $font ?>"><?= $sampleText ?></h1>
                        <p class="h1 text-<?= $font ?>"><?= $sampleText ?></p>
                        <p class="h2 text-<?= $font ?>"><?= $sampleText ?></p>
                        <p class="h3 text-<?= $font ?>"><?= $sampleText ?></p>
                        <p class="h4 text-<?= $font ?>"><?= $sampleText ?></p>
                        <p class="text-<?= $font ?> fw-bold"><?= $sampleText ?></p>
                        <p class="text-<?= $font ?> fw-bolder"><?= $sampleText ?></p>
                        <p class="text-<?= $font ?> fw-semibold"><?= $sampleText ?>.</p>
                        <p class="text-<?= $font ?> fw-medium"><?= $sampleText ?></p>
                        <p class="text-<?= $font ?> fw-normal"><?= $sampleText ?></p>
                        <p class="text-<?= $font ?> fw-light"><?= $sampleText ?></p>
                        <p class="text-<?= $font ?> fw-lighter"><?= $sampleText ?></p>
                        <p class="text-<?= $font ?> fst-italic"><?= $sampleText ?></p>
                        <p class="text-<?= $font ?> fst-normal"><?= $sampleText ?></p>
                        <p class="lead text-<?= $font ?>">
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                            Vivamus massa sapien, maximus eget bibendum id, dignissim cursus quam.
                            Nullam pharetra eget arcu eget interdum. Orci varius natoque penatibus
                            et magnis dis parturient montes, nascetur ridiculus mus. Quisque sit
                            amet ligula efficitur, aliquet sapien ut, porttitor mi. Aenean euismod
                            urna eget metus efficitur mattis. Pellentesque nisl erat, consequat eu
                            consequat quis, porttitor sed neque. Etiam sodales sapien vitae sodales
                            scelerisque. Aenean sit amet tempor neque. Donec commodo lorem felis,
                            ut mollis felis posuere volutpat.
                        </p>
                        <p class="text-<?= $font ?>">
                            Sed odio enim, mollis ac nulla vel, elementum sodales urna. Sed
                            tristique pretium leo blandit malesuada. Nulla eget orci eu enim
                            venenatis facilisis. Orci varius natoque penatibus et magnis dis
                            parturient montes, nascetur ridiculus mus. Donec vel turpis pretium,
                            sagittis ligula ullamcorper, aliquet arcu. Ut massa est, convallis ac
                            nibh sit amet, tempus rutrum odio. Nullam eget urna ac orci fermentum
                            venenatis vel et lacus. Duis at urna sagittis, fringilla lorem at,
                            rhoncus urna. Sed non odio et libero maximus vehicula non quis tortor.
                            Duis porta urna vel fringilla sodales. Donec a felis in arcu scelerisque
                            pretium. Maecenas eu commodo nisl. Duis elit odio, rutrum vitae sem et,
                            maximus mollis justo. Duis augue augue, pulvinar vel dui et, imperdiet
                            scelerisque urna.
                        </p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
