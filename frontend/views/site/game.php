<style>
    .hero-section {
        padding: 4rem 0 6rem;
    }

    .hero-text {
        font-size: 1.25rem;
        line-height: 1.6;
        color: var(--dragon-text-muted);
        margin-bottom: 2rem;
    }

    .hero-text .initial {
        color: var(--dragon-amber);
        font-weight: 600;
        font-size: 1.5rem;
    }

    .btn-dragon {
        background-color: var(--dragon-amber);
        border-color: var(--dragon-amber);
        color: var(--dragon-dark);
        font-weight: 600;
        padding: 0.75rem 2rem;
        font-size: 1.1rem;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        transition: all 0.2s ease;
    }

    .btn-dragon:hover {
        background-color: var(--dragon-amber-hover);
        border-color: var(--dragon-amber-hover);
        color: var(--dragon-dark);
        box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
        transform: translateY(-2px);
    }

    .btn-outline-dragon {
        border-color: var(--dragon-text-muted);
        color: var(--dragon-text-muted);
        padding: 0.75rem 2rem;
        font-size: 1.1rem;
        transition: all 0.2s ease;
    }

    .btn-outline-dragon:hover {
        background-color: var(--dragon-slate);
        border-color: var(--dragon-text);
        color: var(--dragon-text);
    }

    .hero-image {
        border-radius: 1rem;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        position: relative;
        overflow: hidden;
    }

    .hero-image::before {
        content: '';
        position: absolute;
        inset: -1rem;
        background: linear-gradient(45deg, rgba(245, 158, 11, 0.2), rgba(251, 146, 60, 0.2));
        border-radius: 1.5rem;
        z-index: -1;
        filter: blur(20px);
    }

    .content-section {
        padding: 5rem 0;
    }

    .content-card {
        background: rgba(30, 41, 59, 0.6);
        border: 1px solid var(--dragon-slate-light);
        border-radius: 1rem;
        padding: 3rem;
        height: 100%;
        backdrop-filter: blur(10px);
        transition: transform 0.2s ease;
    }

    .content-card:hover {
        transform: translateY(-4px);
    }

    .content-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--dragon-amber);
        margin-bottom: 2rem;
        line-height: 1.2;
    }

    .content-text {
        font-size: 1.1rem;
        line-height: 1.7;
        color: var(--dragon-text-muted);
        margin-bottom: 1.5rem;
    }

    .content-text .initial {
        color: var(--dragon-amber);
        font-weight: 600;
        font-size: 1.3rem;
    }

    .section-divider {
        height: 1px;
        background: var(--dragon-slate-light);
        border: none;
        margin: 0;
    }

</style>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class=" text-decoration">
                            Venture into a Realm of Dragons!
                        </h1>

                        <div class="hero-text">
                            <p class="mb-4">
                                <span class="initial">E</span>mbark on an epic adventure with As the Dragon Flies, where you can create your character, explore a vast interactive map, and connect with fellow adventurers.
                            </p>
                            <p class="mb-0">
                                <span class="initial">J</span>oin our community and weave your legend today!
                            </p>
                        </div>

                        <div class="d-flex flex-column flex-sm-row gap-3 mt-4">
                            <button class="btn btn-dragon btn-lg">
                                <i class="fas fa-dragon me-2"></i>Come and join us!
                            </button>
                            <button class="btn btn-outline-dragon btn-lg">
                                Learn More
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
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
    </section>

    <hr class="section-divider">

    <!-- Content Sections -->
    <section class="content-section">
        <div class="container">
            <div class="row g-5">
                <!-- Mission and Values -->
                <div class="col-lg-6">
                    <div class="content-card">
                        <h2 class="content-title">Our Mission and Values</h2>

                        <div class="content-text">
                            <p>
                                The As the Dragon Flies game was imagined in 2021 by a passionate team of Dungeons and Dragons enthusiasts. Our mission is to provide an engaging and interactive online gaming experience that captures the essence of epic gaming.
                            </p>

                            <p>
                                We believe in creativity, fostering a welcoming community, and maintaining integrity in gameplay. Our team is dedicated to building a vibrant platform where players can explore, create, and connect in a rich fantasy world.
                            </p>

                            <p class="fw-semibold" style="color: #fbbf24;">
                                Join us on this adventure!
                            </p>
                        </div>

                        <button class="btn btn-dragon">
                            <i class="fas fa-dragon me-2"></i>Come and join us!
                        </button>
                    </div>
                </div>

                <!-- Epic Adventure -->
                <div class="col-lg-6">
                    <div class="content-card">
                        <h2 class="content-title">Embark on an Epic Adventure with As the Dragon Flies!</h2>

                        <div class="content-text">
                            <p>
                                <span class="initial">D</span>ive into a world of fantasy and adventure with an online role game experience.
                            </p>

                            <p>
                                <span class="initial">C</span>reate your character, explore a vast map, and interact with fellow adventurers in real-time.
                            </p>

                            <p>
                                <span class="initial">E</span>xperience the thrill of collaborative storytelling and strategic gameplay from the comfort of your home.
                            </p>
                        </div>

                        <button class="btn btn-dragon">
                            <i class="fas fa-dragon me-2"></i>Come and join us!
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
