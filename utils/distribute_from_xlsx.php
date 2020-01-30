<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/class/class_db_handler.php";
require_once "$homedir/config.php";
require_once "$homedir/class/class_shifts_distributor.php";
require "$homedir/vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

if (!isset($_GET['m'])) {
    echo 'Error: $_GET["m"] not set!';
    exit;
}

class XlsxRespondsUploader extends DBHandler
{
    public function __construct($master_handler, $config_handler)
    {
        $this->dbh = $master_handler->dbh;
        $this->id_user = $master_handler->id_user;
        $this->http_host = $config_handler->http_host;
        $this->sleepSeconds = $config_handler->sleepSeconds;
        /** Create a new Xls Reader  **/
        $this->reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        /** Load $inputFileName to a Spreadsheet Object  **/
        $inputFileName = "$config_handler->homedir/data/xlsx/google_sheet_responds.xlsx";
        /** Load $inputFileName to a Spreadsheet Object  **/
        $this->spreadsheet = $this->reader->load($inputFileName);
        $this->worksheet = $this->spreadsheet->getActiveSheet();

        $this->m = $_GET['m'];
        $this->setIdxRange();
        $this->setArrDate();
        $this->process();
    }

    private function setArrDate()
    {
        $this->arrDate = [];
        for ($iCol = 3; $iCol <= $this->colHighestIdx; $iCol++) {
            $colName = $this->worksheet->getCellByColumnAndRow($iCol, 0)->getValue(); // '16日(月)'
            $this->arrDate[$iCol] = intval(substr($colName, 0, strspn($colName, "0123456789"))); // $this->arrDate[3] = 16 ... 6 ...
        }
    }

    private function setIdxRange()
    {
        // Set row range
        $this->rowHighestIdx = $this->worksheet->getHighestRow();
        $this->colHighestIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($this->worksheet->getHighestColumn()); // e.g. 7
    }

    public function process()
    {
        $this->beginTransactionIfNotIn();
        $this->deleteAppIfAny();
        $this->uploadApp();
        // Transaction goes on for ShiftDistributor. Don't commit.
    }

    private function deleteAppIfAny()
    {
        $sql = "DELETE FROM shifts_submitted WHERE m='$this->m';";
        echo "Deleting any applications:<br>$sql<br>";
        $this->executeSql($sql);
    }

    private function uploadApp()
    {
        $this->addAllSql();
        echo "SQLS:<br> $this->SQLS<br>";
        // $this->executeSql($this->SQLS);
    }

    private function addAllSql()
    {
        for ($iRow = 1; $iRow <= $this->rowHighestIdx; $iRow++) {
            $this->addSql($iRow);
        }
    }

    private function addSql($iRow)
    {
        $id_user = $this->getIdUser($iRow); // 5
        $arrSqlCols = [];
        $arrSqlVals = [];
        for ($iCol = 3; $iCol <= $this->colHighestIdx; $iCol++) {
            $strApp = $this->worksheet->getCellByColumnAndRow($iCol, $iRow)->getValue(); // 'B, O' or ''
            $arrDateShifts = explode(', ', $strApp); // ['B', 'O'] or []
            foreach ($arrDateShifts as $key => $shift) {
                $arrDateShifts[$key] = $this->arrDate[$iCol] . $shift; // ['17B', '17O']...
                $arrSqlVals[] = 1; // [1, 1]...
            }
            $arrSqlCols = array_merge($arrSqlCols, $arrDateShifts); // ['id_user', 'm' , '16A', '17B', '17O']
        }
        $sql = "INSERT INTO shifts_submitted (id_user, m, " . implode(', ', $arrSqlCols) . ") VALUES ($id_user, '$this->m', " . implode(', ', $arrSqlVals) . ');';
        echo "Insert applications for id_user $id_user:<br>$sql<br>";
        $this->SQLS = $this->SQLS . $sql;
    }

    private function getIdUser($iRow)
    {
        $name = $this->worksheet->getCellByColumnAndRow(2, $iRow)->getValue(); // '05KimHweemyoung'
        $id_user = substr($name, 0, strspn($name, "0123456789")); // '05'
        if (!intval($id_user)) {
            echo "ERROR: Couldn't locate correct id_user from name value :$name on row $iRow<br>";
            exit;
        }
        return intval($id_user); // 5
    }
}

$test = true;
$host = 'localhost';
$DBName = 'gairyo';
$userName = 'root';
$pw = '111111';
$dbh = new PDO("mysql:host=$host;dbname=$DBName", "$userName", "$pw", array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));

// Upload responds to shifts_submitted
$xlsx_responds_uploader = new XlsxRespondsUploader($master_handler, $config_handler);
// Then distribute shifts
// $shifts_distributor =  new ShiftsDistributor($master_handler, $config_handler);