<?php

use frontend\helpers\Caligraphy;
use frontend\widgets\Button;
use yii\helpers\Url;
?>


<style>
<?php if (1 === 1): ?>
        .hero-section {
            min-height: 75vh;
            max-width: 1200px;
            display: flex;
            margin: auto;
            padding: 10px;
            align-items: center;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--dark-gray-50);
            z-index: 0;
        }

        .hero-section .carousel {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .hero-section .carousel-item {
            height: 100vh;
        }

        .hero-section .carousel-item img {
            object-fit: cover;
            width: 100%;
            height: 100%;
            filter: brightness(0.4); /* Optional: darken the image for better text contrast */
        }

        .hero-section .container {
            z-index: 1;
            position: relative;
        }

<?php endif; ?>
</style>
<body>
    <!-- Hero Section -->
    <section class="hero-section position-relative">
        <div class="carousel slide carousel-fade transition: transform 2s ease, opacity 2s ease-out d-none d-md-block" data-bs-ride="carousel">
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
        <div class="container position-relative z-index-1">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <h1 class="text-warning text-decoration">Venture into a Realm of Dragons!</h1>
                    <br />
                    <p style="font-weight: 100">
                        <?=
                        Caligraphy::illuminate([
                            'Embark on an epic adventure with ' . Caligraphy::appName() . ',
                                     where you can create your character, explore a vast interactive map,
                                     and connect with fellow adventurers.',
                            'Join our community and weave your legend today!'
                                ],
                                'lead text-decoration'//style
                        )
                        ?>
                    </p>
                    <br>

                    <div class="d-flex flex-column flex-sm-row gap-3 mt-4 justify-content-center">
                        <?=
                        Button::widget([
                            'icon' => 'dnd-logo h2',
                            'url' => Url::toRoute(['site/login']),
                            'title' => 'Come and join us!',
                            'style' => 'btn-lg text-decoration w-auto',
                            'callToAction' => true
                        ])
                        ?>
                        <?=
                        Button::widget([
                            'icon' => 'bi-hand-index',
                            'title' => 'Learn More',
                            'style' => 'btn-lg text-decoration w-auto',
                        ])
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <hr>

    <!-- Content Sections -->
    <section>
        <div class="container">
            <div class="row g-5">
                <!-- Mission and Values -->
                <div class="col-lg-6">
                    <div class="card p-5 h-100">
                        <h2 class="text-warning text-decoration">Our Mission and Values</h2>
                        <?=
                        Caligraphy::illuminate([
                            'The As the Dragon Flies game was imagined in 2021 by a passionate'
                            . ' team of Dungeons and Dragons enthusiasts. Our mission is to provide'
                            . ' an engaging and interactive online gaming experience that captures'
                            . ' the essence of epic gaming.',
                            'We believe in creativity, fostering a welcoming community, and maintaining'
                            . ' integrity in gameplay. Our team is dedicated to building a vibrant platform'
                            . ' where players can explore, create, and connect in a rich fantasy world.'
                                ],
                                'text-decoration'//style
                        )
                        ?>

                        <div class="d-flex justify-content-center">
                            <?=
                            Button::widget([
                                'icon' => 'dnd-logo',
                                'url' => Url::toRoute(['site/login']),
                                'title' => 'Come and join us!',
                                'style' => 'text-decoration',
                            ])
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Epic Adventure -->
                <div class="col-lg-6">
                    <div class="card p-5 h-100">
                        <h2 class="text-warning text-decoration">Embark on an Epic Adventure with As the Dragon Flies!</h2>

                        <?=
                        Caligraphy::illuminate([
                            'Dive into a world of fantasy and adventure with an online role game experience.',
                            'Create your character, explore a vast map, and interact with fellow adventurers in real-time.',
                            'Experience the thrill of collaborative storytelling and strategic gameplay from the comfort of your home.'
                                ],
                                'text-decoration'//style
                        )
                        ?>

                        <div class="d-flex justify-content-center">
                            <?=
                            Button::widget([
                                'icon' => 'dnd-logo',
                                'url' => Url::toRoute(['site/login']),
                                'title' => 'Come and join us!',
                                'style' => 'text-decoration',
                            ])
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
