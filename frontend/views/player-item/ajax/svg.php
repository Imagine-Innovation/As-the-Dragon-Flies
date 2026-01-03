<?php
/** @var yii\web\View $this */
/** @var array $playerBodyData */
/** @var bool $withId */
/** @var bool $withOffcanvas */
/** @var array<string, array{cx: int, cy: int, r: int, x: int, y: int, size: int, fill: string, opacity: string}> $circles */
$circles = [
    'equipmentHeadZone' => [
        'cx' => 200,
        'cy' => 90,
        'r' => 90,
        'x' => 110,
        'y' => 0,
        'size' => 180,
        'fill' => 'white',
        'opacity' => '0.5',
    ],
    'equipmentChestZone' => [
        'cx' => 200,
        'cy' => 330,
        'r' => 120,
        'x' => 80,
        'y' => 210,
        'size' => 240,
        'fill' => 'white',
        'opacity' => '0.5',
    ],
    'equipmentRightHandZone' => [
        'cx' => 70,
        'cy' => 520,
        'r' => 70,
        'x' => 0,
        'y' => 450,
        'size' => 140,
        'fill' => 'white',
        'opacity' => '0.5',
    ],
    'equipmentLeftHandZone' => [
        'cx' => 330,
        'cy' => 520,
        'r' => 70,
        'x' => 260,
        'y' => 450,
        'size' => 140,
        'fill' => 'white',
        'opacity' => '0.5',
    ],
    'equipmentBackZone' => [
        'cx' => 395,
        'cy' => 150,
        'r' => 70,
        'x' => 325,
        'y' => 80,
        'size' => 140,
        'fill' => 'black',
        'opacity' => '0.5',
    ],
];

$suffix = 0 + ($withId ? 0 : 1) + ($withOffcanvas ? 1 : 0);
foreach ($playerBodyData as $zone => $playerBody) {
    // Check if itemId exists AND if the zone is actually defined in our $circles array
    if (!empty($playerBody['itemId']) && isset($circles[$zone])) {
        $circles[$zone]['fill'] = "url(#pattern-{$zone}{$suffix})";
        $circles[$zone]['opacity'] = '1';
    }
}
?>
<div class="card-body svg-container">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 900" preserveAspectRatio="xMidYMid meet" <?= $withId ? 'id="equipmentSvg"' : 'style="max-height: 30vh;"' ?>>
        <?php foreach ($playerBodyData as $zone => $playerBody): ?>
            <?php
            // Ensure the zone exists in our SVG coordinate map
            if (!empty($playerBody['itemId']) && isset($circles[$zone])):
                $circle = $circles[$zone];
                ?>
                <defs>
                    <pattern id="pattern-<?= $zone ?><?= $suffix ?>" patternUnits="userSpaceOnUse" width="<?= $circle['size'] ?>" height="<?= $circle['size'] ?>" x="<?= $circle['x'] ?>" y="<?= $circle['y'] ?>">
                        <image href="/frontend/web/img/item/<?= $playerBody['image'] ?>" width="<?= $circle['size'] ?>" height="<?= $circle['size'] ?>"/>
                    </pattern>
                </defs>
            <?php endif; ?>
        <?php endforeach; ?>
        <image href="/frontend/web/img/man-back-view.png" width="200" height="450" x=300 y=0 class="img-fluid" />
        <image href="/frontend/web/img/man-front-view.png" width="400" height="900" class="img-fluid" />

        <?php if ($withId): ?>
            <?php foreach ($circles as $zone => $circle): ?>
                <circle id="<?= $zone ?>" cx="<?= $circle['cx'] ?>" cy="<?= $circle['cy'] ?>" r="<?= $circle['r'] ?>" fill="<?= $circle['fill'] ?>" fill-opacity="<?= $circle['opacity'] ?>" style="cursor: pointer;"/>
            <?php endforeach; ?>
        <?php else: ?>
            <?php foreach ($circles as $zone => $circle): ?>
                <circle cx="<?= $circle['cx'] ?>" cy="<?= $circle['cy'] ?>" r="<?= $circle['r'] ?>" fill="<?= $circle['fill'] ?>" fill-opacity="<?= $circle['opacity'] ?>" />
            <?php endforeach; ?>
        <?php endif; ?>
    </svg>
</div>
