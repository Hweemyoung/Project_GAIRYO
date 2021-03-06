<header>Shifts</header>
<main>
    <section id="section-main">
    <a class="a-popover" data-toggle="popover" data-content="Here can users check shifts assigned to all members." data-trigger="hover" data-placement="bottom">Tabs</a>
        <ul class="nav nav-tabs">
            <?php
            ?>
            <li class="nav-item"><a href="#tab-content1" class="nav-link <?php if (!isset($_GET["page"]) && !isset($_GET["Y"])) {
                                                                                echo 'active';
                                                                            } ?>" data-toggle="tab">My Shifts</a>
            </li>
            <li class="nav-item"><a href="#tab-content2" class="nav-link <?php if (isset($_GET["page"]) || isset($_GET["Y"])) {
                                                                                echo 'active';
                                                                            } ?>" data-toggle="tab">Daily Members</a>
            </li>
            <li class="nav-item"><a href="#tab-content3" class="nav-link" data-toggle="tab">Submit</a>
            </li>
            <!-- <li class="nav-item"><a href="#tab-content3" class="nav-link active" data-toggle="tab">Market</a>
                </li> -->
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade <?php if (!isset($_GET["page"])) {
                                            echo 'active show';
                                        } ?>" id="tab-content1"><?php require './tab_my_shifts.php'; ?></div>
            <div class="tab-pane fade <?php if (isset($_GET["page"])) {
                                            echo 'active show';
                                        } ?>" id="tab-content2"><?php require './tab_daily_members.php'; ?></div>
            <div class="tab-pane fade" id="tab-content3">
                <?php if ($config_handler->enableSubmit) {
                    $Y = intval(substr($config_handler->m_submit, 0 ,4));
                    $m = intval(substr($config_handler->m_submit, -2 ,2));
                    echo strtr('
                    <script>
                        const submitMonth = $m;
                        const submitYear = $Y;
                    </script>
                    ', array('$m' => $m, '$Y' => $Y));
                    require './tab_submit_shifts.php';
                } else {
                    echo "
                    <p>$config_handler->message</p>
                        ";
                }
                ?>
            </div>
        </div>
    </section>
</main>