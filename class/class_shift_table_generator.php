<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/class/class_db_handler.php";

class ShiftTableGenerator extends DBHandler
{
    public function __construct($config_handler)
    {
        // $host = 'sql304.epizy.com';
        // $DBName = 'epiz_24956964_gairyo';
        // $userName = 'epiz_24956964';
        // $pw = 'STZDGxr4iOPDhv';
        $host = 'localhost';
        $DBName = 'gairyo_shift_dist';
        $userName = 'root';
        $pw = '111111';
        $this->dbh = new PDO("mysql:host=$host;dbname=$DBName", "$userName", "$pw", array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
        // var_dump($this->dbh);
        $this->m = $_POST['m'];
        $this->homedir = $config_handler->homedir;
        $this->http_host = $config_handler->http_host;
        $this->fp = "$this->http_host/data/csv/new_shift_table.csv";
        $this->arrShiftsByIdUser = [];
        $this->arrCsv = [];
        $this->weekdays = [0 => '月', 1 => '火', 2 => '水', 3 => '木', 4 => '金', 5 => '土', 6 => '日'];

        $this->process();
    }

    public function process()
    {
        $this->beginTransactionIfNotIn();
        $this->setRange();
        $this->setArrShiftsByIdUsers();
        $this->genCsv();
    }

    private function setRange()
    {
        $this->Y = intval(substr($this->m, 0, 4));
        $this->n = intval(substr($this->m, -2, 2));
        $dateTime = DateTime::createFromFormat('Ymd', $this->m . '01'); // 2020-03-01;
        $this->dateEnd = $dateTime->setDate($dateTime->format('Y'), $dateTime->format('n'), 15)->format('Y-m-d'); // '2020-03-15'
        // echo "dateEnd: $this->dateEnd<br>";
        $prevMonth = DateTime::createFromFormat('Ymd', $this->m . '01');
        $prevMonth->modify('-1 days'); // 2020-02-28;
        $this->lastDateOfPrevMonth = $prevMonth->format('j');
        // echo "lastDateOfPrevMonth: $this->lastDateOfPrevMonth<br>";
        $this->firstDateTime = $prevMonth->setDate($prevMonth->format('Y'), $prevMonth->format('n'), 16);
        $this->dateStart = $this->firstDateTime->format('Y-m-d'); // '2020-02-16'
        // echo "dateStart: $this->dateStart<br>";
        $this->iColMax = $this->lastDateOfPrevMonth + 3; // [id_user, lang, name, 16, 17, ..., 14, 15, name]
        // echo "iColMax: $this->iColMax<br>";
    }

    private function setArrShiftsByIdUsers()
    {
        // Get cn first
        $sql = "SELECT id_user, nickname, cn FROM members WHERE cn=1 ORDER BY id_user ASC";
        $stmt = $this->querySql($sql);
        $arrMembersOfCnByIdUser = $stmt->fetchAll(PDO::FETCH_UNIQUE);
        $stmt->closeCursor();
        // var_dump($arrMembersOfCnByIdUser);
        $this->arrShiftsByIdUser = $this->arrShiftsByIdUser + $this->loadShiftsForIdUsers($arrMembersOfCnByIdUser);

        // Get rest
        $sql = "SELECT id_user, nickname, cn FROM members WHERE cn<>1 ORDER BY id_user ASC";
        $stmt = $this->querySql($sql);
        $arrMembersOfNotCnByIdUser = $stmt->fetchAll(PDO::FETCH_UNIQUE);
        $stmt->closeCursor();
        $this->arrShiftsByIdUser = $this->arrShiftsByIdUser + $this->loadShiftsForIdUsers($arrMembersOfNotCnByIdUser);

        // Save arrMembersByIdUser
        $this->arrMembersByIdUser = $arrMembersOfCnByIdUser + $arrMembersOfNotCnByIdUser;
        // echo "Keys of arrShiftsByIdUser:<br>";
        // var_dump(array_keys($this->arrShiftsByIdUser));
        // echo '<br>';
    }

    private function loadShiftsForIdUsers($arrMembersByIdUsers)
    {
        $sql = "SELECT id_user, id_user, date_shift, shift FROM shifts_assigned WHERE (date_shift>='$this->dateStart' AND date_shift<='$this->dateEnd') AND (id_user IN (" . implode(', ', array_keys($arrMembersByIdUsers)) . ")) ORDER BY id_user ASC, date_shift ASC;";
        // echo $sql . '<br>';
        $stmt = $this->querySql($sql);
        $arrShiftsByIdUser = $stmt->fetchAll(PDO::FETCH_GROUP);
        // echo "arrShiftsByIdUser:<br>";
        // var_dump($arrShiftsByIdUser);
        // echo '<br>';
        $stmt->closeCursor();
        return $arrShiftsByIdUser; // [[11]=>[0=>['id_user'=>11, 'date_shift'=>'2020-01-17', 'shift'=>'B'], 1=>[...], ...], [13]=>...] $arrShiftsByIdUser[id_user][$key][$col]
    }

    private function genCsv()
    {
        if (!count($this->arrShiftsByIdUser)) {
            echo 'No shifts loaded. Exit!<br>';
            exit;
        }
        // First generate array
        $this->genCsvArrays();
        // Fill array with blank;
        $this->fillArrays();
        echo '<br>Building arrCsv Completed<br>';
        // var_dump($this->arrCsv);
        // Convert array to CSV
        $this->toCsv();
    }

    private function genCsvArrays()
    {
        // 1st row
        $this->arrCsv[0] = [2 => '小田急旅行センター 新宿西口勤務表', 12 => strval($this->Y), 14 => '年', 15 => strval($this->n), 16 => '月分'];

        // 2nd row
        $part1 = [1 => '言語', 2 => '日付'];
        $part2 = array_combine(range(3, $this->lastDateOfPrevMonth + 2), array_merge(range(16, $this->lastDateOfPrevMonth), range(1, 15)));
        $this->arrCsv[1] = $part1 + $part2;

        // 3rd row
        $part1 = [2 => '氏　名　／　曜　日'];
        $arrDays = [];
        $firstDay = intval($this->firstDateTime->format('N')) - 1;
        for ($i = 0; $i < $this->lastDateOfPrevMonth; $i++) {
            $arrDays[] = $this->weekdays[($firstDay + $i) % 7];
        }
        $part2 = array_combine(range(3, $this->lastDateOfPrevMonth + 2), $arrDays);
        $this->arrCsv[2] = $part1 + $part2;

        // Build Rows
        foreach ($this->arrShiftsByIdUser as $id_user => $arrShifts) { // $arrShiftsByIdUser[id_user][$key][$col]
            $this->arrCsv[] = $this->genCsvArray($id_user, $arrShifts);
        }

        // Next rows
        $this->arrCsv[] = $this->arrCsv[2];
        $this->arrCsv[] = $this->arrCsv[1];
    }

    private function genCsvArray($id_user, $arrShifts)
    {
        // $arrShifts[$key][$col]
        $arr = [];
        // 1st col
        $arr[0] = $id_user; // '15'
        // 2nd col
        $arr[1] = $this->arrMembersByIdUser[$id_user]['cn']; // '0' or '1';
        // 3rd col
        $arr[2] = $this->arrMembersByIdUser[$id_user]['nickname'];

        foreach ($arrShifts as $key => $arrCase) { // $arrCase[$key][$col]
            $date = intval(substr($arrCase['date_shift'], -2, 2));
            if ($date > 15) {
                $iCol = $date - 13;
            } else {
                $iCol = $this->lastDateOfPrevMonth - 13 + $date;
            }
            $arr[$iCol] = $arrCase['shift'];
        }
        // Last col
        $arr[$this->iColMax] = $this->arrMembersByIdUser[$id_user]['nickname'];
        return $arr;
    }

    private function fillArrays()
    {
        foreach ($this->arrCsv as $iRow => $row) {
            for ($iCol = 0; $iCol <= $this->iColMax; $iCol++) {
                if (!isset($row[$iCol])) {
                    // echo 'Not set!<br>';
                    $this->arrCsv[$iRow][$iCol] = '';
                    // echo "row $iRow col $iCol Is set!:";
                    // var_dump(isset($this->arrCsv[$iRow][$iCol]));
                    // echo '<br>';
                } elseif (gettype($row[$iCol]) !== 'string') {
                    // echo 'Not String!<br>';
                    $this->arrCsv[$iRow][$iCol] = strval($this->arrCsv[$iRow][$iCol]);
                }
            }
            ksort($this->arrCsv[$iRow]);
        }
    }

    private function toCsv()
    {

        $f = fopen("$this->homedir/data/csv/new_shift_table.csv", 'w');
        foreach ($this->arrCsv as $row) {
            fputcsv($f, $row);
        }
        fclose($f);
    }
}