<main>
    <div class="container px-1">
        <section id="section-main">
            <ul class="nav nav-tabs">
                <li class="nav-item"><a href="#tab-content1" class="nav-link" data-toggle="tab">My Shifts</a>
                <li>
                <li class="nav-item"><a href="#tab-content2" class="nav-link" id="tab-daily-members" data-toggle="tab">Daily Members</a>
                </li>
                <li class="nav-item"><a href="#tab-content3" class="nav-link active" data-toggle="tab">Market</a>
                </li>
            </ul>
            <div class="tab-content">
            <?php
            require './tab_my_shifts.php';
            ?>
            </div>
        </section>
    </div>
</main>