<?php

use frontend\widgets\Button;
use yii\helpers\Url;

/** @var yii\web\View $this */
/**
 * State 0 (initial sta
 */
?>
<div class="row d-flex justify-content-center g-3">
    <div class="col-12 col-sm-10 col-lg-9 col-xl-8">
        <section id="level1">
            <h1 class="text-decoration text-yellow">Wellcome back</h1>
            <div class="card mb-3 rounded">
                <div class="row g-0">
                    <div class="col-md-4">
                        <img src="resources/story-2/img/Grimhold castle.png" class="img-fluid rounded-start" alt="...">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <h5 class="card-title text-decoration">Card title</h5>
                            <p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
                            <p class="card-text"><small class="text-body-secondary">Last updated 3 mins ago</small></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="col-12 col-sm-10 col-lg-9 col-xl-8">
        <section id="level2">
            <div class="row d-flex justify-content-center">
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card">
                        <img src="img/sm/story.png" class="card-img-top" alt="Find a story">
                        <div class="card-body">
                            <p class="card-text">We've put together a list of stories that we think you'll love!</p>
                            <?=
                            Button::widget([
                                'url' => Url::toRoute('story/index'),
                                'icon' => 'dnd-scroll',
                                'style' => 'text-decoration w-100',
                                'tooltip' => 'Find a new quest',
                                'title' => 'Browse all stories',
                                'isCta' => true,
                            ])
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card mb-3 rounded">
                        xx
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card mb-3 rounded">
                        xx
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="col-12 col-sm-10 col-lg-9 col-xl-8">
        <section id="level3">
            <p>level 3</p>
        </section>
    </div>
</div>
