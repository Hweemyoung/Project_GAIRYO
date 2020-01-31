<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/config.php";

class SignupFormHandler
{
    public function __construct()
    {
        $this->check_post();
    }

    private function check_post()
    {
        if (!isset($_GET['id_google'])) {
            echo 'Try sign-in again!';
            exit;
        }
    }
}

$signup_form_handler = new SignupFormHandler();
?>
<main>
    <section id="section-signup">
        <div class="row">
            <div class="col-sm-2"></div>
            <div class="col-sm-8">
                <div class="card">
                    <div class="card-body">
                        <h1 class="text-center">Welcome to Gairyo!</h1>
                        <p class="text-center">必要項目を記入し、提出してください。管理者の承認次第、ご利用になれます。</p>
                        <form action="<?= $config_handler->http_host ?>/process/signup.php" method="POST">
                            <input type="hidden" name="id_google" value=<?= $_GET['id_google'] ?>>
                            <div class="form-group form-group-required">
                                <div class="row">
                                    <div class="col-sm-4 d-flex align-items-center">
                                        <div class="text-danger ml-sm-auto">*</div><label for="input-last-name">姓</label>
                                    </div>
                                    <div class="col-sm-8"><input type="text" class="form-control" id="input-last-name" name="last_name" value="<?= $_GET['last_name'] ?>"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-sm-4 d-flex align-items-center"><label class="ml-sm-auto" for="input-middle-name">中間名</label></div>
                                    <div class="col-sm-8"><input type="text" class="form-control" id="input-middle-name" name="middle_name" value="<?php if (isset($_GET['middle_name'])) {
                                                                                                                                                        echo $_GET['middle_name'];
                                                                                                                                                    } ?>"></div>
                                </div>
                            </div>
                            <div class="form-group form-group-required">
                                <div class="row">
                                    <div class="col-sm-4 d-flex align-items-center">
                                        <div class="text-danger ml-sm-auto">*</div><label for="input-first-name">名前</label>
                                    </div>
                                    <div class="col-sm-8"><input type="text" class="form-control" id="input-first-name" name="first_name" value="<?= $_GET['first_name'] ?>"></div>
                                </div>
                            </div>
                            <div class="form-group form-group-required">
                                <div class="row">
                                    <div class="col-sm-4 d-flex align-items-center">
                                        <div class="text-danger ml-sm-auto">*</div><label for="input-nick-name">呼び名</label>
                                    </div>
                                    <div class="col-sm-8"><input type="text" class="form-control" id="input-nick-name" name="nickname" value="<?= $_GET['first_name'] ?>"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-sm-4 d-flex align-items-center"><label class="ml-sm-auto">駆使言語</label></div>
                                    <div class="col-sm-8 d-flex justify-content-center flex-wrap">
                                        <?php
                                        // echo $config_handler->numLangs . '<br>';
                                        // var_dump($config_handler->arrayLangsShort) . '<br>';
                                        // var_dump($config_handler->arrayLangsLong) . '<br>';
                                        for ($i = 0; $i < $config_handler->numLangs; $i++) {
                                            // echo $i;
                                            $langShort = $config_handler->arrayLangsShort[$i];
                                            $langLong = $config_handler->arrayLangsLong[$langShort];
                                            echo "
                                                <div class='custom-control custom-control-inline custom-checkbox m-1' style='width: 7rem'>
                                                    <input type='checkbox' id='$langShort' class='custom-control-input' name='$langShort' value='1'><label for='$langShort' class='custom-control-label'>$langLong</label>
                                                </div>";
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="form-group">
                            <div class="row">
                                <div class="col-sm-4 d-flex align-items-center"><label class="ml-sm-auto">駆使言語</label></div>
                                <div class="col-sm-8 d-flex justify-content-center flex-wrap">
                                    <div class="custom-control custom-control-inline custom-checkbox m-1" style="width: 7rem"><input type="checkbox" id="cn" class="custom-control-input" name="cn" value="1"><label for="cn" class="custom-control-label">Chinese</label>
                                    </div>
                                    <div class="custom-control custom-control-inline custom-checkbox m-1" style="width: 7rem"><input type="checkbox" id="kr" class="custom-control-input" name="kr" value="1"><label for="kr" class="custom-control-label">Korean</label>
                                    </div>
                                    <div class="custom-control custom-control-inline custom-checkbox m-1" style="width: 7rem"><input type="checkbox" id="th" class="custom-control-input" name="th" value="1"><label for="th" class="custom-control-label">Thailand</label>
                                    </div>
                                    <div class="custom-control custom-control-inline custom-checkbox m-1" style="width: 7rem"><input type="checkbox" id="my" class="custom-control-input" name="my" value="1"><label for="my" class="custom-control-label">Malaysian</label>
                                    </div>
                                    <div class="custom-control custom-control-inline custom-checkbox m-1" style="width: 7rem"><input type="checkbox" id="ru" class="custom-control-input" name="ru" value="1"><label for="ru" class="custom-control-label">Russian</label>
                                    </div>
                                    <div class="custom-control custom-control-inline custom-checkbox m-1" style="width: 7rem"><input type="checkbox" id="fr" class="custom-control-input" name="fr" value="1"><label for="fr" class="custom-control-label">French</label>
                                    </div>
                                    <div class="custom-control custom-control-inline custom-checkbox m-1" style="width: 7rem"><input type="checkbox" id="de" class="custom-control-input" name="de" value="1"><label for="de" class="custom-control-label">Deutsche</label>
                                    </div>
                                    <div class="custom-control custom-control-inline custom-checkbox m-1" style="width: 7rem"><input type="checkbox" id="other" class="custom-control-input" name="other" value="1"><label for="other" class="custom-control-label">Others</label>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                            <hr>
                            <div class="div-buttons text-center">
                                <button id="btn-submit" class="btn btn-primary disabled" type="submit" title="Sign Up">Sign Up</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>