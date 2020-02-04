<?php
// f: from [0=> NULL, 1=>register_agree.php, 2=>upload_transaction.php, 3=>signup.php]
// AlertHandler::_from = $_GET[f]
// AlertHandler::_status: [0=> error, 1=> success]
// AlertHandler::_value: value of corresponding status
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/config.php";

class AlertHandler
{
    public function __construct($__FILE__, $master_handler, $config_handler)
    {
        // From none
        if (!isset($_GET["f"])) {
            $this->_from = 0;
        } else {
            $this->_from = intval($_GET["f"]);
        }
        $this->master_handler = $master_handler;
        $this->basename = basename($__FILE__);
        $this->config_handler = $config_handler;
        $this->init();
    }

    private function init()
    {
        if ($this->_from) {
            $this->setStatus();
            $this->setMsg();
            echo $this->basename;
        }
    }

    private function setStatus()
    {
        if ($this->_from) {
            if (isset($_GET["e"])) {
                $this->_status = 0;
                $this->_value = intval($_GET["e"]);
                $this->alertMode = 'alert-danger';
                $this->alertStrong = 'Failed!';
            } else if (isset($_GET["s"])) {
                $this->_status = 1;
                $this->_value = $_GET["s"];
                $this->alertMode = 'alert-success';
                $this->alertStrong = 'Success!';
            }
        }
    }
    private function setMsg()
    {
        switch ($this->basename) {
            case 'transactions.php':
                switch ($this->_from) {
                    case 0:
                        break;
                        // From register_agree.php
                    case 1:
                        switch ($this->_status) {
                            case 0:
                                switch ($this->_value) {
                                    case 0:
                                        $this->alertMsg = 'No permission.';
                                        break;
                                    case 1:
                                        $this->alertMsg = 'Some requests had already been closed.';
                                        break;
                                    case 2:
                                        $this->alertMsg = 'The request had already been agreed with by the user.';
                                        break;
                                    case 3:
                                        $date_shift = $_GET['date'];
                                        $partName = $this->config_handler->arrayPartNames[$_GET['part']];
                                        $nick_to = $this->master_handler->arrayMemberObjectsByIdUser[$_GET['id_to']]->nickname;
                                        $this->alertMsg = "Member '$nick_to' already has a shift in $partName";
                                        break;
                                    case 4:
                                        $arrayCases = [];
                                        foreach ($_GET as $key => $value) {
                                            if (substr($key, 0, 4) === 'case') {
                                                $temp = explode('_', $value);
                                                $dateTime = DateTime::createFromFormat('Y-m-d', $temp[0]);
                                                $date = $dateTime->format('Y M j (D)');
                                                $partName = $this->arrayPartNames[$temp[1]];
                                                $langLong = $this->config_handler->arrayLangsLong[$temp[2]];
                                                $msg = "$date $partName の $langLong";
                                                array_push($arrayCases, $msg);
                                            }
                                        }
                                        $this->alertMsg = "Following languages are not sufficient: " . implode(', ', $arrayCases);
                                        break;
                                }
                                break;
                            case 1:
                                switch ($this->_value) {
                                    case '0':
                                        $this->alertMode = 'alert-warning';
                                        $this->alertStrong = 'Agreed!';
                                        $this->alertMsg = 'Awaiting other agreements for executing transaction.';
                                        break;
                                    case '1':
                                        $this->alertMsg = 'Transaction successfully executed.<br>Your Shifts have been UPDATED!';
                                        break;
                                    case '2':
                                        $this->alertMsg = 'Transaction successfully declined and no longer valid.';
                                        break;
                                }
                                break;
                        }
                        break;
                        // From upload_transaction.php
                    case 2:
                        switch ($this->_status) {
                            case 0:
                                switch ($this->_value) {
                                    case 0:
                                        $this->alertMsg = 'Invalid form ID. Call for administrator.';
                                        break;
                                    case 1:
                                        $nick = $this->master_handler->arrayMemberObjectsByIdUser[$_GET["id_from"]]->nickname;
                                        $dateShift = $_GET["date"];
                                        $shift = $_GET["shift"];
                                        $this->alertMsg = "Shift doesn't exist!: $nick's $dateShift $shift";
                                        break;
                                }
                                break;
                            case 1:
                                switch ($this->_value) {
                                    case 0:
                                        $this->alertMsg = 'Transaction successfully created.';
                                        break;
                                }
                                break;
                        }
                        break;
                        // From upload_market_item.php
                    case 4:
                        switch ($this->_status) {
                            case 0:
                                switch ($this->_value) {
                                    case 0:
                                        $this->alertMsg = 'Mode not set.';
                                        break;
                                    case 1:
                                        $this->alertMsg = "Mode not understood.";
                                        break;
                                    case 2:
                                        $nick_user = $this->master_handler->arrayMemberObjectsByIdUser[$_GET['id_user']]->nickname;
                                        $nick_from = $this->master_handler->arrayMemberObjectsByIdUser[$_GET['id_from']]->nickname;
                                        $this->alertMsg = "User $nick_user is handling other member $nick_from's shift!";
                                        break;
                                    case 3:
                                        $nick_user = $this->master_handler->arrayMemberObjectsByIdUser[$_GET['id_user']]->nickname;
                                        $nick_to = $this->master_handler->arrayMemberObjectsByIdUser[$_GET['id_to']]->nickname;
                                        $this->alertMsg = "User $nick_user is handling other member $nick_to's shift!";
                                        break;
                                    case 4:
                                        $var_name = $_GET['var'];
                                        $this->alertMsg = "Variable '$var_name' is required but not given!";
                                        break;
                                    case 5:
                                        $this->alertMsg = "No such shift found!";
                                        break;
                                    case 6:
                                        $id_request = $_GET['id_request'];
                                        $this->alertMsg = "Item already exists in market: id_request=$id_request";
                                        break;
                                    case 7:
                                        $date = $_GET['date'];
                                        $shift = $_GET['shift'];
                                        $this->alertMsg = "You already have shift '$shift' on $date. Remove it first.";
                                        break;
                                }
                                break;
                            case 1:
                                switch ($this->_value) {
                                    case 0:
                                        $date_shift = $_GET['date'];
                                        $shift = $_GET['shift'];
                                        $this->alertMsg = "Your shift '$shift' on $date_shift has been put to market.";
                                        break;
                                    case 1:
                                        $date_shift = $_GET['date'];
                                        $shift = $_GET['shift'];
                                        $this->alertMsg = "You're now calling for shift '$shift' on $date_shift.";
                                        break;
                                    case 2:
                                        $date_shift = $_GET['date_shift'];
                                        $shift = $_GET['shift'];
                                        $nick_to = $this->master_handler->arrayMemberObjectsByIdUser[$_GET['id_to']]->nickname;
                                        $this->alertMsg = "Matched: Your shift '$shift' on $date_shift was taken by $nick_to!";
                                        break;
                                    case 3:
                                        $date_shift = $_GET['date_shift'];
                                        $shift = $_GET['shift'];
                                        $nick_from = $this->master_handler->arrayMemberObjectsByIdUser[$_GET['id_from']]->nickname;
                                        $this->alertMsg = "Matched: You got shift '$shift' on $date_shift from $nick_from!";
                                        break;
                                }
                                break;
                        }
                        break;
                }
                break;
            case 'admin.php':
                echo 'here1';
                switch ($this->_from) {
                        // From signup.php
                    case 3:
                        echo 'here2';
                        switch ($this->_status) {
                                // Failed
                            case 0:
                                echo 'here3';
                                switch ($this->_value) {
                                    case 0:
                                        $this->alertMsg = 'Failed to register.';
                                        break;
                                    case 1:
                                        $col = $_GET['col'];
                                        $this->alertMsg = "Following item is missing: $col";
                                        break;
                                }
                                break;
                                // Success
                            case 1:
                                switch ($this->_value) {
                                    case 0:
                                        $this->alertMsg = 'Registration successful. Please wait for authentication';
                                        break;
                                }
                                break;
                        }
                        break;
                }
                break;
            case 'shifts.php':
                switch ($this->_from) {
                        // From marketplace.php
                    case 5:
                        switch ($this->_status) {
                                // Success
                            case 1:
                                switch ($this->_value) {
                                    case 0:
                                        $this->alertMode = 'alert-warning';
                                        $this->alertStrong = 'Choose!';
                                        $this->alertMsg = 'Please choose your shift to put.';
                                        break;
                                }
                        }
                }
        }
    }

    public function getAlertArray()
    {
        if ($this->_from) {
            return ['alertMode' => $this->alertMode, 'alertStrong' => $this->alertStrong, 'alertMsg' => $this->alertMsg];
        } else {
            return [];
        }
    }
}
