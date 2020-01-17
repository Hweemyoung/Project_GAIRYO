<?php
require_once './config.php';

class SignupFormHandler
{
    public function __construct()
    {
        $this->check_post();
    }

    private function check_post()
    {
        if (!isset($_POST['id_google'])) {
            echo 'Try sign-in again!';
            exit;
        }
    }
}

$signup_form_handler = new SignupFormHandler();
?>

<section id="section-signup">
    <div class="row">
        <div class="col-sm-2"></div>
        <div class="col-sm-8">
            <div class="card">
                <div class="card-body">
                    <form action="./register_member.php" method="POST">
                        <input type="hidden" name="id_google" value="<?= $_POST['id_google'] ?>">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-4"><label for="input-last-name">姓/Last Name</label></div>
                                <div class="col-sm-8"><input type="text" class="form-control" id="input-last-name" name="last_name" value="<?= $_POST['last_name'] ?>"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-4"><label for="input-middle-name">中間名/Middle Name</label></div>
                                <div class="col-sm-8"><input type="text" class="form-control" id="input-middle-name" name="middle_name" value="<?php if (isset($_POST['middle_name'])) {
                                                                                                                                                    echo $_POST['middle_name'];
                                                                                                                                                } ?>"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-4"><label for="input-first-name">名前/First Name</label></div>
                                <div class="col-sm-8"><input type="text" class="form-control" id="input-first-name" name="first_name" value="<?= $_POST['first_name'] ?>"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-4"><label for="input-nick-name">呼び名/Nickname</label></div>
                                <div class="col-sm-8"><input type="text" class="form-control" id="input-nick-name" name="nickname" value="<?= $_POST['first_name'] ?>"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-4"><label>駆使言語/Lingualities</label></div>
                                <div class="col-sm-8">
                                    <?php
                                    for ($i = 0; $i < count($config_handler->numLangs); $i++) {
                                        $langShort = $config_handler->arrayLangsShort[$i];
                                        $langLong = $config_handler->arrayLangsLong[$i];
                                        echo "
                                    <div class='form-check-inline'><label for='$langShort' class='form-check-label'><input type='checkbox' class='form-check-input' name='$langShort' value='1'>$langLong</label></div>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>