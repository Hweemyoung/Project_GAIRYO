<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/class/class_shifts_distributor.php";
require_once "$homedir/check_session_shift_dist.php";
require_once "$homedir/config.php";
$config_handler->m = $_POST['m'];
public $defaultNumMaxByShift = ['A' => 1, 'B' => 4, 'H' => 2, 'C' => 2, 'D' => 4];
public $arrNumMaxByShiftByDate = [];
// public $arrNumMaxByShiftByDate = [16 => ['A' => 1, 'B' => 6, 'H' => 4, 'C' => 3, 'D' => 6]];

public $defaultNumNeededByShift = ['H' => 1, 'C' => 1];
public $arrNumNeededByShiftByDate = [];
// public $arrNumNeededByShiftByDate = [16 => ['B' => 3, 'H' => 2, 'C' => 2, 'D' => 4]];

public $defaultNumNeededByPart = [5, 4];
public $arrNumNeededByPartByDate = [];
// public $arrNumNeededByPartByDate = [16 => [8, 8]];

public $defaultArrLangsByPart = [['cn' => 2, 'kr' => NULL, 'th' => NULL, 'my' => NULL, 'ru' => NULL, 'fr' => NULL, 'de' => NULL, 'other' => NULL], ['cn' => 2, 'kr' => NULL, 'th' => NULL, 'my' => NULL, 'ru' => NULL, 'fr' => NULL, 'de' => NULL, 'other' => NULL]];
    public $arrLangsByDate = [16 => [['cn' => 4, 'kr' => NULL, 'th' => NULL, 'my' => NULL, 'ru' => NULL, 'fr' => NULL, 'de' => NULL, 'other' => NULL], ['cn' => 4, 'kr' => NULL, 'th' => NULL, 'my' => NULL, 'ru' => NULL, 'fr' => NULL, 'de' => NULL, 'other' => NULL]]];

$shift_distributor = new ShiftsDistributor($master_handler, $config_handler);