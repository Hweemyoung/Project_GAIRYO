<?php

use function utils\customVarDump;

$homedir = '/home/vol15_8/epizy.com/epiz_24956964/htdocs';
require_once "$homedir/class/class_shifts_distributor.php";
require_once "$homedir/class/class_shift_table_generator.php";
require_once "$homedir/check_session_shift_dist.php";

function getIntValOfPost(string $key)
{
    var_dump($_POST[$key]);
    echo "<br>";
    if ($_POST[$key] === "0") {
        $val = 0;
    } else {
        $val = intval($_POST[$key]);
        if ($val === 0) {
            echo "$key not understood";
            exit;
        }
    }
    return $val;
}

var_dump($_POST);
echo "<br>";

if (intval($_POST['m']) === 0 || strlen($_POST['m']) !== 6) {
    echo "有効な年月が与えられていません。";
    exit;
}

require_once "$homedir/config.php";
$config_handler->m = $_POST['m'];
$arrShifts = ["A", "B", "H", "C", "D"];

// "max_part_0";
// "max_part_1";
// "min_A";
// "max_A";
// "min_B";
// "max_B";
// "min_H";
// "max_H";
// "min_C";
// "max_C";
// "min_D";
// "max_D";
// "cn_part_0"
// "cn_part_1"

// Default
// Max_part
$arrNumNeededByPart = [];
for ($part = 0; $part < 2; $part++) {
    // Validate max_part_$part
    if ($_POST["default" . "_max_part_$part"] === "") {
        echo "default" . "_max_part_$part" . " must be given!";
        exit;
    }
    $val = getIntValOfPost("default" . "_max_part_$part");
    $arrNumNeededByPart[$part] = $val;
}
$config_handler->defaultNumNeededByPart = $arrNumNeededByPart;

// Max_$shift
$arrNumMaxByShift = [];
foreach ($arrShifts as $shift) {
    // Validate max_$shift
    if ($_POST["default" . "_max_$shift"] === "") {
        echo "default" . "_max_$shift" . " must be given!";
        exit;
    }
    $val = getIntValOfPost("default" . "_max_$shift");
    $arrNumMaxByShift[$shift] = $val;
}
// Add to config_handler
$config_handler->defaultNumMaxByShift = $arrNumMaxByShift;

// Min_$shift
$arrNumNeededByShift = [];
foreach ($arrShifts as $shift) {
    // Validate min_$shift
    if ($_POST["default" . "_min_$shift"] !== "") {
        $val = getIntValOfPost("default" . "_min_$shift");
        $arrNumNeededByShift[$shift] = $val;
    }
}
// Add to config_handler
if (boolval($arrNumNeededByShift)) {
    $config_handler->defaultNumNeededByShift = $arrNumNeededByShift;
}

// cn
// public $defaultArrLangsByPart = [['cn' => 2], ['cn' => 2]]; // デフォルト値
// public $arrLangsByDate = [];
// public $arrLangsByDate = [16 => [['kr' => 3, 'de' => 3], []]]; // 指定しない場合 右辺を[]にすること。
$arrLangsByPart = [];
for ($part = 0; $part < 2; $part++) {
    if ($_POST["default" . "_cn_part_$part"] !== "") {
        $val = getIntValOfPost("default" . "_cn_part_$part");
        $val = $val === 0 ? [] : ['cn' => $val];
    } else {
        $val = [];
    }
    $arrLangsByPart[$part] = $val;
}
$config_handler->defaultArrLangsByPart = $arrLangsByPart;

// For specific dates
for ($itemID = 1; $itemID < $_POST["num_items"] + 1; $itemID++) {
    // For each item(date)
    if ($_POST["$itemID" . "_date"] !== "") {
        $date = intval($_POST["$itemID" . "_date"]);
        // Validate date
        if ($date === 0) {
            echo "Date not understood. exit!";
            exit;
        }

        // Max_part
        $arrNumNeededByPart = [];
        for ($part = 0; $part < 2; $part++) {
            // Validate max_part_$part
            if ($_POST["$itemID" . "_max_part_$part"] === "") {
                echo "$itemID" . "_max_part_$part" . " must be given!";
                exit;
            }
            $val = getIntValOfPost("$itemID" . "_max_part_$part");
            $arrNumNeededByPart[$part] = $val;
        }
        $config_handler->arrNumNeededByPartByDate[$date] = $arrNumNeededByPart;

        // Max_$shift
        $arrNumMaxByShift = [];
        foreach ($arrShifts as $shift) {
            // Validate max_$shift
            if ($_POST["$itemID" . "_max_$shift"] === "") {
                echo "$itemID" . "_max_$shift" . " must be given!";
                exit;
            }
            $val = getIntValOfPost("$itemID" . "_max_$shift");
            $arrNumMaxByShift[$shift] = $val;
        }
        // Add to config_handler
        $config_handler->arrNumMaxByShiftByDate[$date] = $arrNumMaxByShift;

        // Min_$shift
        $arrNumNeededByShift = [];
        foreach ($arrShifts as $shift) {
            // Validate min_$shift
            if ($_POST["$itemID" . "_min_$shift"] !== "") {
                $val = getIntValOfPost("$itemID" . "_min_$shift");
                $arrNumNeededByShift[$shift] = $val;
            }
        }
        // Add to config_handler
        if (boolval($arrNumNeededByShift)) {
            $config_handler->arrNumNeededByShiftByDate[$date] = $arrNumNeededByShift;
        }

        // cn
        // public $defaultArrLangsByPart = [['cn' => 2], ['cn' => 2]]; // デフォルト値
        // public $arrLangsByDate = [];
        // public $arrLangsByDate = [16 => [['kr' => 3, 'de' => 3], []]]; // 指定しない場合 右辺を[]にすること。
        $arrLangsByPart = [];
        for ($part = 0; $part < 2; $part++) {
            if ($_POST["$itemID" . "_cn_part_$part"] !== "") {
                $val = getIntValOfPost("$itemID" . "_cn_part_$part");
                $val = $val === 0 ? [] : ['cn' => $val];
            } else {
                $val = [];
            }
            $arrLangsByPart[$part] = $val;
        }
        if (boolval($arrNumNeededByShift)) {
            $config_handler->arrLangsByDate[$date] = $arrLangsByPart;
        }
    }
}
// consec_jp
// consec_fo
// weekly_mins_jp
// weekly_mins_fo
// weekly_days_jp
// weekly_days_fo
// monthly_mins_jp
// monthly_mins_fo
// monthly_days_jp
// monthly_days_fo
// 日本人・外国人の最大連続出社日
// public $maxConsecutiveWorkedDatesByJp = ['0' => 2, '1' => 2];
// 日本人・外国人の週間最長労働時間(分)
// public $maxWorkedMinsPerWeekByJp = ['0' => 1600, '1' => 1600];
// 日本人・外国人の週間最多労働日数
// public $maxWorkedDaysPerWeekByJp = ['0' => 5, '1' => 5];
// 日本人・外国人の月間最長労働時間
// public $maxWorkedMinsPerMonthByJp = ['0' => 12000, '1' => 12000];
// 日本人・外国人の月間最多労働日数
// public $maxWorkedDaysPerMonthByJp = ['0' => 20, '1' => 20];
$arrItems = ["consec", "weekly_mins", "weekly_days", "monthly_mins", "monthly_days"];
$arrNat = ["fo", "jp"];
// consec
foreach($arrNat as $i => $nat){
    $key = "consec_$nat";
    if ($_POST[$key] === "") {
        echo "$key must be given!";
        exit;
    }
    $val = getIntValOfPost($key);
    $config_handler->maxConsecutiveWorkedDatesByJp[$i] = $val - 1;

    $key = "weekly_mins_$nat";
    if ($_POST[$key] === "") {
        echo "$key must be given!";
        exit;
    }
    $val = getIntValOfPost($key);
    $config_handler->maxWorkedMinsPerWeekByJp[$i] = $val;

    $key = "weekly_days_$nat";
    if ($_POST[$key] === "") {
        echo "$key must be given!";
        exit;
    }
    $val = getIntValOfPost($key);
    $config_handler->maxWorkedDaysPerWeekByJp[$i] = $val;

    $key = "monthly_mins_$nat";
    if ($_POST[$key] === "") {
        echo "$key must be given!";
        exit;
    }
    $val = getIntValOfPost($key);
    $config_handler->maxWorkedMinsPerMonthByJp[$i] = $val;

    $key = "monthly_days_$nat";
    if ($_POST[$key] === "") {
        echo "$key must be given!";
        exit;
    }
    $val = getIntValOfPost($key);
    $config_handler->maxWorkedDaysPerMonthByJp[$i] = $val;
}

echo "<br>";
echo "defaultNumNeededByPart<br>";
var_dump($config_handler->defaultNumNeededByPart);
echo "<br>";
echo "defaultNumMaxByShift<br>";
var_dump($config_handler->defaultNumMaxByShift);
echo "<br>";
echo "defaultNumNeededByShift<br>";
var_dump($config_handler->defaultNumNeededByShift);
echo "<br>";
echo "arrNumNeededByPartByDate<br>";
var_dump($config_handler->arrNumNeededByPartByDate);
echo "<br>";
echo "arrNumMaxByShiftByDate<br>";
var_dump($config_handler->arrNumMaxByShiftByDate);
echo "<br>";
echo "arrNumNeededByShiftByDate<br>";
var_dump($config_handler->arrNumNeededByShiftByDate);
echo "<br>";
echo "defaultArrLangsByPart<br>";
var_dump($config_handler->defaultArrLangsByPart);
echo "<br>";
echo "arrLangsByDate<br>";
var_dump($config_handler->arrLangsByDate);
echo "<br>";
echo "maxConsecutiveWorkedDatesByJp";
var_dump($config_handler->maxConsecutiveWorkedDatesByJp);
echo "<br>";
echo "maxWorkedMinsPerWeekByJp";
var_dump($config_handler->maxWorkedMinsPerWeekByJp);
echo "<br>";
echo "maxWorkedDaysPerWeekByJp";
var_dump($config_handler->maxWorkedDaysPerWeekByJp);
echo "<br>";
echo "maxWorkedMinsPerMonthByJp";
var_dump($config_handler->maxWorkedMinsPerMonthByJp);
echo "<br>";
echo "maxWorkedDaysPerMonthByJp";
var_dump($config_handler->maxWorkedDaysPerMonthByJp);
echo "<br>";
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

// public $defaultArrLangsByPart = [['cn' => 2], ['cn' => 2]]; // デフォルト値
// public $arrLangsByDate = [];
// public $arrLangsByDate = [16 => [['kr' => 3, 'de' => 3], []]]; // 指定しない場合 右辺を[]にすること。

$shift_distributor = new ShiftsDistributor($master_handler, $config_handler);
$shift_table_generator = new ShiftTableGenerator($master_handler, $config_handler);
?>
<a href="<?= $shift_table_generator->fp ?>">DOWNLOAD csv</a>