<main>
    <div class="container px-1">
        <section id="section-main">
            <ul class="nav nav-tabs">
                <?php
                ?>
                <li class="nav-item"><a href="#tab-content1" class="nav-link <?php if(!isset($_GET["page"])){echo 'active';}?>" data-toggle="tab">My Shifts</a>
                <li>
                <li class="nav-item"><a href="#tab-content2" class="nav-link <?php if(isset($_GET["page"])){echo 'active';}?>" data-toggle="tab">Daily Members</a>
                </li>
                <li class="nav-item"><a href="#tab-content3" class="nav-link" data-toggle="tab">Submit</a>
                </li>
                <!-- <li class="nav-item"><a href="#tab-content3" class="nav-link active" data-toggle="tab">Market</a>
                </li> -->
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade <?php if(!isset($_GET["page"])){echo 'active show';}?>" id="tab-content1"><?php require './tab_my_shifts.php';?></div>
                <div class="tab-pane fade <?php if(isset($_GET["page"])){echo 'active show';}?>" id="tab-content2"><?php require './tab_daily_members.php';?></div>
                <div class="tab-pane fade" id="tab-content3"><?php require './tab_submit_shifts.php';?></div>
            </div>
        </section>
    </div>
</main>