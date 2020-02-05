<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/class/class_shifts_distributor.php";
require_once "$homedir/class/class_shift_table_generator.php";
require_once "$homedir/check_session_shift_dist.php";

if (intval($_POST['m']) === 0 || strlen($_POST['m']) !== 6) {
    echo "有効な年月が与えられていません。";
    exit;
}

if ($_FILES['config']['size'] === 0) {
    require_once "$homedir/config.php";
} else {
    // echo 'here';
    // $config_file = $_FILES['config']['tmp_name'];
    // require_once "$config_file";
}

// var_dump($config_handler);

// $config_handler->m = $_POST['m'];
// public $defaultNumMaxByShift = ['A' => 1, 'B' => 4, 'H' => 2, 'C' => 2, 'D' => 4];
// public $arrNumMaxByShiftByDate = [];
// // public $arrNumMaxByShiftByDate = [16 => ['A' => 1, 'B' => 6, 'H' => 4, 'C' => 3, 'D' => 6]];

// public $defaultNumNeededByShift = ['H' => 1, 'C' => 1];
// public $arrNumNeededByShiftByDate = [];
// // public $arrNumNeededByShiftByDate = [16 => ['B' => 3, 'H' => 2, 'C' => 2, 'D' => 4]];

// public $defaultNumNeededByPart = [5, 4];
// public $arrNumNeededByPartByDate = [];
// // public $arrNumNeededByPartByDate = [16 => [8, 8]];

// public $defaultArrLangsByPart = [['cn' => 2, 'kr' => NULL, 'th' => NULL, 'my' => NULL, 'ru' => NULL, 'fr' => NULL, 'de' => NULL, 'other' => NULL], ['cn' => 2, 'kr' => NULL, 'th' => NULL, 'my' => NULL, 'ru' => NULL, 'fr' => NULL, 'de' => NULL, 'other' => NULL]];
//     public $arrLangsByDate = [16 => [['cn' => 4, 'kr' => NULL, 'th' => NULL, 'my' => NULL, 'ru' => NULL, 'fr' => NULL, 'de' => NULL, 'other' => NULL], ['cn' => 4, 'kr' => NULL, 'th' => NULL, 'my' => NULL, 'ru' => NULL, 'fr' => NULL, 'de' => NULL, 'other' => NULL]]];

$shift_distributor = new ShiftsDistributor($master_handler, $config_handler);
$shift_table_generator = new ShiftTableGenerator($config_handler);
?>
<a href="<?= $shift_table_generator->fp ?>">DOWNLOAD csv</a>