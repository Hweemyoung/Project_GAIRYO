<?php
$signedin = false;
require 'check_session.php';

class AlertHandler
{
    public function __construct()
    {
        // From none
        if (!isset($_GET["f"])) {
            $this->_from = 0;
        } else {
            $this->_from = $_GET["f"];
        }
        $this->init();
    }

    private function init()
    {
        $this->setStatus();
        $this->setMsg();
    }
    private function setStatus()
    {
        if ($this->_from) {
            if (isset($_GET["e"])) {
                $this->_status = 0;
                $this->alertMode = 'alert-danger';
                $this->alertStrong = 'Failed!';
                $this->_value = $_GET["e"];
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
        switch ($this->_from) {
            case 0:
                break;
                // From register_agree.php
            case 1:
                switch ($this->_status) {
                    case 0:
                        $this->alertMode = 'alert-danger';
                        $this->alertStrong = 'Failed!';
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
                        }
                        break;
                    case 1:
                        switch ($this->_value) {
                            case 0:
                                $this->alertMode = 'alert-warning';
                                $this->alertStrong = 'Agreed!';
                                $this->alertMsg = 'Awaiting other agreements for executing transaction.';
                                break;
                            case 1:
                                $this->alertMsg = 'Transaction successfully executed.<br>Your Shifts have been UPDATED!';
                                break;
                            case 2:
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
                        $this->alertMode = 'alert-danger';
                        $this->alertStrong = 'Failed!';
                        switch ($this->_value) {
                            case 0:
                                $nick = $_GET["nick"];
                                $dateShift = $_GET["date"];
                                $shift = $_GET["shift"];
                                $this->alertMsg = "Shift doesn't exist!: $nick's $dateShift $shift";
                                break;
                            case 1:
                                $id_request = $_GET["idrequest"];
                                $id_transaction = $_GET["idtrans"];
                                $nickname_from = $_GET["nickfrom"];
                                $dateShift = $_GET["date"];
                                $shift = $_GET["shift"];
                                $nickname_to = $_GET["nickto"];
                                $this->alertMsg = "Request already exists.<br>Request#$id_request in Transaction#$id_transaction: $nickname_from's $dateShift $shift to $nickname_to";
                                break;
                        }
                        break;
                    case 1:
                        switch ($this->_value) {
                            case 0:
                                $this->alertMsg = 'Transaction successfully created.';
                                break;
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

$alertHandler = new AlertHandler();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    require './common_head.php';
    ?>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="./css/transactions.css">
</head>

<body>
    <?php
    require './transactions_header.php';
    if (!$signedin) {
        require './common_nav_signedout.php';
        require './common_main_signedout.php';
    } else {
        require './common_nav_signedin.php';
        require './transactions_signedin.php';
    }
    require './common_footer.php';
    ?>
    <script src="./js/alerthandler.js"></script>
    <script>
        const _alertArray = <?= json_encode($alertHandler->getAlertArray()) ?>;
        const alertHandler = new AlertHandler(<?= json_encode($alertHandler->getAlertArray()) ?>);
    </script>
</body>