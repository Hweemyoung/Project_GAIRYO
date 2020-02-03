<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/class/class_db_handler.php";
require_once "$homedir/class/class_date_shifts_deployer.php";
require_once "$homedir/config.php";

class ShiftsDistributor extends DBHandler
{
    private $arrDateShiftsDeployerByDate;
    private $arrDateRange;
    private $arrStats;
    private $arrDateTimes;
    private $arrDatesByWeek;
    private $arrIdUserAppByDate;

    public function __construct($master_handler, $config_handler)
    {
        $this->dbh = $master_handler->dbh;
        $this->master_handler = $master_handler;
        $this->config_handler = $config_handler;
        $this->arrayMemberObjectsByIdUser = $master_handler->arrayMemberObjectsByIdUser;
        // var_dump(array_keys($this->arrayMemberObjectsByIdUser));
        $this->arrayShiftsByPart = $config_handler->arrayShiftsByPart;
        $this->m = $config_handler->m;
        $this->init();
        $this->process();
    }

    private function init()
    {
        $this->arrDateShiftsDeployerByDate = [];
        $this->arrDateRange = [];
        $this->arrStats = [];
        $this->arrDateTimes = [];
        $this->arrDatesByWeek = [];
        $this->addPropsToMemberObjects(); // memberObject->numDaysApplied = 0; memberObject->numDaysDeployed = 0; memberObject->arrShiftAppObjects = [];
        $this->arrIdUserAppByDate = [];
    }

    private function addPropsToMemberObjects()
    {
        foreach ($this->arrayMemberObjectsByIdUser as $memberObject) {
            $memberObject->initProps();
        }
    }

    public function process()
    {
        $this->beginTransactionIfNotIn();
        $this->loadShiftsSubmitted();
        $this->setArrDateShiftsHandlerByDate();
        $this->deleteAllIfAny();
        $this->distributeAllShifts();
        $this->dbh->commit();
    }

    private function deleteAllIfAny()
    {
        $dateStart = $this->arrDateTimes[16]->format('Y-m-d');
        $dateEnd = $this->arrDateTimes[15]->format('Y-m-d');
        $sql = "DELETE FROM shifts_assigned WHERE date_shift>='$dateStart' AND date_shift<='$dateEnd';";
        $this->executeSql($sql);
    }

    private function loadShiftsSubmitted()
    {
        $sql = "SELECT id_user, " . implode(', ', $this->config_handler->set_arr_mshifts()->arr_mshifts) . " FROM shifts_submitted WHERE m='$this->m'";
        $stmt = $this->querySql($sql);
        $this->arrMemberApplicationsByIdUser = $stmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
        // echo $sql .'<br>';
        // var_dump(array_keys($this->arrMemberApplicationsByIdUser));
        // Some values (e.g. 31st) could be NULL
        $stmt->closeCursor();
        return $this;
    }

    private function setArrDateShiftsHandlerByDate()
    {
        $this->arrDateShiftsDeployerByDate = [];
        $reflectionShiftObject = new ReflectionClass('ShiftObject');
        // echo "Current DateTime<br>";
        // var_dump($dateTime->format('Y-m-d'));
        // echo '<br>';
        foreach (range(1, 31) as $date) {
            $dateTime = DateTime::createFromFormat('Ymd', $this->m . '01'); // '2020-03-01'
            if ($date === 15) {
                $this->maxDateTime = $dateTime;
            } elseif ($date > 15) {
                $dateTime = $dateTime->modify('-1 days'); // '2020-02-28';
                if ($date === 16) {
                    $this->minDateTime = $dateTime;
                }
                // echo 'Modified DateTime:' . $dateTime->format('Y-m-d') . '<br>';
                if ($date > 28) {
                    // Check if $date could be valid DateTime
                    if (!checkdate($dateTime->format('n'), $date, $dateTime->format('Y'))) { // If like '2020-02-30'
                        echo "For date = $date, this datetime is NOT valid <br>";
                        break;
                    }
                    echo "For date = $date, this datetime is valid <br>";
                }
            }
            // Add date to arrDateRange
            $this->arrDateRange[] = $date;
            $dateTime->setDate($dateTime->format('Y'), $dateTime->format('n'), $date);

            $this->arrDateTimes[$date] = $dateTime;
            $this->arrDatesByWeek[$dateTime->format('W')][] = $date;

            echo "Modified DateTime<br>";
            var_dump($dateTime->format('Y-m-d'));
            echo '<br>';
            $this->arrDateShiftsDeployerByDate[$date] = new DateShiftsDeployer($date, $this->master_handler, $this->config_handler);
            // echo "$date 's DateShiftsDeployer: <br>";
            // var_dump($this->arrDateShiftsDeployerByDate[$date]);
            // echo '<br>';
            $appliedForDate = false;
            foreach (['O', 'A', 'B', 'H', 'C', 'D'] as $shift) {
                // echo "Now $shift! <br>";
                // Set properties
                // 1. Push all ShiftAppObjects
                foreach (array_keys($this->arrMemberApplicationsByIdUser) as $id_user) {
                    if ($this->arrMemberApplicationsByIdUser[$id_user][$date . $shift] == 1) {
                        // echo "\$this->arrMemberApplicationsByIdUser[$id_user][$date$shift] = '1'<br><br>";
                        if ($shift === 'O') {
                            foreach ($this->arrayShiftsByPart as $arrShifts) {
                                foreach ($arrShifts as $shiftTemp) {
                                    // Create date_shift
                                    $shiftObject = $this->genShiftAppObject($reflectionShiftObject, $id_user, $dateTime, $shiftTemp);
                                    // echo "Created ShiftObject: id_user: $shiftObject->id_user / date_shift: $shiftObject->date_shift / shift: $shiftObject->shift<br>";
                                    $this->pushShiftAppObject($shiftObject);
                                }
                            }
                        } else {
                            // If person applied for this
                            $shiftObject = $this->genShiftAppObject($reflectionShiftObject, $id_user, $dateTime, $shift);
                            // echo "Created ShiftObject: id_user: $shiftObject->id_user / date_shift: $shiftObject->date_shift / shift: $shiftObject->shift<br>";
                            $this->pushShiftAppObject($shiftObject);
                        }
                        $appliedForDate = true;
                    }
                    $this->updateNumDaysApplied($id_user, $appliedForDate);
                }
            }
            // Save id_user of applicants for this date
            $this->addArrIdUserApp($date);
        }

        // echo 'keys of arrShiftStatusByShift<br>';
        // var_dump(array_keys($this->arrDateShiftsDeployerByDate[16]->arrShiftStatusByShift));
        // echo '<br>';
        // $this->arrDateShiftsDeployerByDate[16]->deployAllShifts();
        // echo '<br> Deployment Completed: <br>';
        // foreach ($this->arrDateShiftsDeployerByDate[16]->arrayShiftObjectsByShift as $shift => $arrShiftObjects) {
        //     echo "$shift<br>";
        //     foreach ($arrShiftObjects as $shiftObject) {
        //         echo "id_user : $shiftObject->id_user<br>";
        //     }
        // }
        // $this->arrDateShiftsDeployerByDate[16]->assignAllShifts($this);
        // var_dump($this->arrDateShiftsDeployerByDate[16]->arrNumLangsAppByPart);
        // echo '<br>';
        // var_dump($this->arrDateShiftsDeployerByDate[16]->arrScoresByIdUser);
        // echo '<br><br>';
        // echo 'arrShiftAppObjectsByIdUser: <br>';
        // var_dump($this->arrDateShiftsDeployerByDate[16]->arrShiftAppObjectsByIdUser);
        // var_dump($this->arrDateShiftsDeployerByDate['2020-02-10']->arrShiftAppObjectsByIdUser);
        // var_dump(($this->arrDateShiftsDeployerByDate)['2020-02-16']);
    }

    private function addArrIdUserApp($date)
    {
        $this->arrIdUserAppByDate[$date] = array_keys($this->arrDateShiftsDeployerByDate[$date]->arrShiftAppObjectsByIdUser);
    }

    private function updateNumDaysApplied($id_user, $appliedForDate)
    {
        // echo '<br>';
        // echo $id_user;
        // echo '<br>';
        if ($appliedForDate) {

            // echo $id_user . ' Before: ' . $this->arrayMemberObjectsByIdUser[$id_user]->numDaysApplied . '<br>';
            $this->arrayMemberObjectsByIdUser[$id_user]->numDaysApplied++;
            // echo $id_user . ' After: ' . $this->arrayMemberObjectsByIdUser[$id_user]->numDaysApplied . '<br>';
        }
    }

    private function genShiftAppObject($reflectionShiftObject, $id_user, $dateTime, $shift)
    {
        $shiftObject = $reflectionShiftObject->newInstanceWithoutConstructor();
        $shiftObject->id_user = $id_user;
        $shiftObject->date = intval($dateTime->format('j')); // int(23)
        $shiftObject->date_shift = $dateTime->format('Y-m-d'); // '2020-02-23'
        $shiftObject->shift = $shift;
        $shiftObject->__construct($this->master_handler, $this->config_handler);
        return $shiftObject;
    }

    private function pushShiftAppObject($shiftObject)
    {
        // To MemberObject->arrShiftAppObjects
        // $shiftObject->memberObject->pushShiftAppObjects($shiftObject);
        // To DateShiftsDeployer->arrShiftAppObjectsByIdUser and to DateShiftsDeployer...ShiftPartStatus->arrShiftAppObjectsByIdUser
        $this->arrDateShiftsDeployerByDate[$shiftObject->date]->pushShiftAppObject($shiftObject);
        // To ShiftStatus->arrShiftAppObjectsByIdUser
        // $this->arrDateShiftsDeployerByDate[$shiftObject->date]->pushShiftAppObjectToShiftStatus($shiftObject);
    }

    private function distributeAllShifts()
    {
        // Shuffle range of date
        shuffle($this->arrDateRange);
        foreach ($this->arrDateRange as $date) {
            echo "Deploying date $date<br>";
            // Deploy all shifts
            $this->arrDateShiftsDeployerByDate[$date]->deployAllShifts();
            // Statistics
            $this->arrStats[$date] = $this->arrDateShiftsDeployerByDate[$date]->getStatistics();
            // Assign all shifts
            $this->arrDateShiftsDeployerByDate[$date]->assignAllShifts($this);
            // Filter ShiftObjects
            $this->filterByWorkingConditions($date);
        }
        $this->getTotalStats();
    }

    private function filterByWorkingConditions($date)
    {
        $curDateTime = $this->arrDateTimes[$date];

        foreach ($this->arrayMemberObjectsByIdUser as $id_user => $memberObject) {
            $is_jp = intval($memberObject->jp);
            $W = $curDateTime->format('W');
            // per week
            // worked days
            if (isset($memberObject->arrWorkedDatesByWeek[$W])) {
                if (count($memberObject->arrWorkedDatesByWeek[$W]) === $this->config_handler->maxWorkedDaysPerWeekByJp[$is_jp]) {
                    echo "This member fully worked for week $W. Unset all ShiftAppObjects for this week.<br>";
                    foreach ($this->arrDatesByWeek[$W] as $date) {
                        unset($this->arrDateShiftsDeployerByDate[$date]->arrShiftAppObjectsByIdUser[$id_user]);
                    }
                }
            }
            // Per month
            // worked days (=numDaysDeployed)
            if ($memberObject->numDaysDeployed === $this->config_handler->maxWorkedDaysPerMonthByJp[$is_jp]) {
                echo "This member fully worked for month. Unset all ShiftAppObjects for this month.<br>";
                foreach ($this->arrDateShiftsDeployerByDate as $dateShiftsDeployer) {
                    unset($dateShiftsDeployer->arrShiftAppObjectsByIdUser[$id_user]);
                }
            }
        }
    }

    private function getTotalStats()
    {
        echo '<br>Total Stats<br>';
        $aveVacancy = 0;
        $divider_1 = 0;

        $arrSumBalances = [];
        $arrSumDividers = [];
        $arrNumPartsNotEnoughLang = [];

        $numDays = 0;
        foreach ($this->arrStats as $date => $stats) {
            $numDays++;
            foreach ($stats[0] as $shiftPart => $shiftPartStatus) {
                $aveVacancy += $shiftPartStatus->vacancy;
                $divider_1++;
            }
            foreach ($stats[1] as $shiftPart => $arrBalances) {
                foreach ($arrBalances as $lang => $balance) {
                    if (!isset($arrSumBalances[$lang])) {
                        $arrSumBalances[$lang] = 0;
                        $arrSumDividers[$lang] = 0;
                    }
                    $arrSumBalances[$lang] += $balance;
                    $arrSumDividers[$lang]++;
                    if ($balance < 0) {
                        if (!isset($arrNumPartsNotEnoughLang[$lang])) {
                            $arrNumPartsNotEnoughLang[$lang] = 0;
                            $arrNumPartsNotEnoughLangDividers[$lang] = 0;
                        }
                        $arrNumPartsNotEnoughLang[$lang]++;
                    }
                }
            }
        }

        $aveVacancy = $aveVacancy / $divider_1;
        echo "Average vacancy: $aveVacancy<br>";
        foreach ($arrSumBalances as $lang => $sumBalance) {
            $aveBal = $sumBalance / $arrSumDividers[$lang];
            echo "Average balance of part for $lang: $aveBal<br>";
        }
        foreach ($arrNumPartsNotEnoughLang as $lang => $numPartsNotEnoughLang) {
            $notEnoughPartRatio = $numPartsNotEnoughLang / $numDays / 2;
            echo "Not enough part ratio for $lang: $notEnoughPartRatio<br>";
        }
        $sumDeployRatio = 0;
        $sumSquareDeployRatio = 0;
        $DRMin = [1];
        $DRMax = [0];
        // Get applicants ids.
        $arrIdUsersAppMerged = [];
        foreach ($this->arrIdUserAppByDate as $date => $arrIdUsersApp) {
            $arrIdUsersAppMerged = array_unique(array_merge($arrIdUsersAppMerged, $arrIdUsersApp));
        }
        $numTotalApplicants = count($arrIdUsersAppMerged);
        echo "Total num of applicants: " . $numTotalApplicants . '<br>';

        foreach ($arrIdUsersAppMerged as $id_user) {
            if ($id_user === 0) {
                continue;
            }
            $memberObject = $this->arrayMemberObjectsByIdUser[$id_user];
            $sumDeployRatio += $memberObject->deployRatio;
            $sumSquareDeployRatio += $memberObject->deployRatio ** 2;
            $DRMin = ($memberObject->deployRatio < array_values($DRMin)[0]) ? [$id_user => $memberObject->deployRatio] : $DRMin;
            $DRMax = ($memberObject->deployRatio > array_values($DRMax)[0]) ? [$id_user => $memberObject->deployRatio] : $DRMax;
        }
        $aveDR = $sumDeployRatio / $numTotalApplicants;
        $stdevDR = sqrt($sumSquareDeployRatio / $numTotalApplicants - $aveDR ** 2);
        echo "Average DR: $aveDR<br>";
        echo "stdev DR: $stdevDR<br>";
        $DRMinVal = array_values($DRMin)[0];
        $DRMinUser = array_keys($DRMin)[0];
        $DRMaxVal = array_values($DRMax)[0];
        $DRMaxUser = array_keys($DRMax)[0];
        echo "DR Min: User $DRMinUser Value: $DRMinVal<br>";
        echo "DR Max: User $DRMaxUser Value: $DRMaxVal<br>";
    }
}
