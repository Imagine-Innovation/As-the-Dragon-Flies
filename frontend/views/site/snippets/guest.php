<?php

use frontend\helpers\Caligraphy;
use yii\helpers\Url;

/* @var $this yii\web\View */
?>

<section id="section-venture">
    <div class="container">
        <div class="content">
            <div class="row justify-content-center">
                <div class="col-12 col-xl-12 col-xxl-9">
                    <div class="row g-5">
                        <div class="col-12 col-lg-6 h-100">
                            <p class="display-4 text-decoration">Venture into a Realm of Dragons!</p>
                            <br />
                            <h5 style="font-weight: 100">
                                <?=
                                Caligraphy::illuminate([
                                    'Embark on an epic adventure with ' . Caligraphy::appName() . ',
                                     where you can create your character, explore a vast interactive map,
                                     and connect with fellow adventurers.',
                                    'Join our community and weave your legend today!'
                                        ],
                                        'lead text-decoration'
                                )
                                ?>
                            </h5>
                            <br>
                            <p>
                                <a class="btn btn-lg btn-warning text-decoration" href="<?= Url::toRoute(['site/login']) ?>">
                                    <img src="img/Dragonfly.svg" style="height:32px;" alt=""> Come and join us!
                                </a>
                            </p>
                        </div>
                        <div class="col-12 col-md-6 h-100">
                            <div class="carousel slide carousel-fade transition: transform 1s ease, opacity 1s ease-out" data-bs-ride="carousel">
                                <div class="carousel-inner" role="listbox">
                                    <div class="carousel-item active">
                                        <img src="img/carousel/car1.jpg" alt="First slide">
                                    </div>
                                    <?php for ($img = 2; $img <= 10; $img++): ?>
                                        <div class="carousel-item" data-aos="fade-up" data-aos-delay="500">
                                            <img src="img/carousel/car<?= $img ?>.jpg" class="d-block h-100">
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="section-mission" class="py-5" style="background-color:rgba(255,255,255,.15);">
    <div class="container">
        <div class="content">
            <div class="row justify-content-center">
                <div class="col-12 col-xl-12 col-xxl-9">
                    <div class="row g-5">
                        <div class="col-12 col-lg-6 h-100">
                            <p class="display-5 text-decoration">Our Mission and Values</p>
                            <br />
                            <h5 style="font-weight: 100">
                                <?=
                                Caligraphy::illuminate([
                                    'The ' . Caligraphy::appName() . ' game was imagined in 2021 by a passionate team of Dungeons and Dragons enthusiasts.
                                    Our mission is to provide an engaging and interactive online gaming experience that captures
                                    the essence of role gaming.',
                                    'We believe in creativity, fostering a welcoming community, and maintaining integrity in gameplay.
                                    Our team is dedicated to building a vibrant platform where players can explore, create, and connect in a rich fantasy world.',
                                    'Join us on this adventure!'
                                        ],
                                        'lead text-decoration'
                                )
                                ?>

                            </h5>
                            <br>
                            <p>
                                <a class="btn btn-lg btn-warning text-decoration" href="<?= Url::toRoute(['site/login']) ?>">
                                    <img src="img/Dragonfly.svg" style="height:32px;" alt=""> Come and join us!
                                </a>
                            </p>
                        </div>
                        <div class="col-12 col-lg-6 h-100">
                            <p class="display-5 text-decoration">Embark on an Epic Adventure with <?= Caligraphy::appName() ?>!</p>
                            <br />
                            <h5 style="font-weight: 100">
                                <?=
                                Caligraphy::illuminate([
                                    'Dive into a world of fantasy and adventure with <?= Caligraphy::appName() ?>, an online role game experience.',
                                    'Create your character, explore a vast map, and interact with fellow adventurers in real-time.',
                                    'Experience the thrill of collaborative storytelling and strategic gameplay from the comfort of your home.'
                                        ],
                                        'lead text-decoration')
                                ?>
                            </h5>
                            <br>
                            <p>
                                <a class="btn btn-lg btn-warning text-decoration" href="<?= Url::toRoute(['site/login']) ?>">
                                    <img src="img/Dragonfly.svg" style="height:32px;" alt=""> Come and join us!
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
