<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/class/class_date_object.php";
require_once "$homedir/class/class_shift_part_status.php";
require_once "$homedir/class/class_shift_status.php";
require_once "$homedir/config.php";
require_once "$homedir/utils.php";

class DateShiftsDeployer extends DateObject
{
    public function __construct($date, $master_handler, $config_handler)
    {
        $this->date = $date;
        $this->master_handler = $master_handler;
        $this->arrayMemberObjectsByIdUser = $master_handler->arrayMemberObjectsByIdUser;
        $this->config_handler = $config_handler;
        $this->arrScoreItems = $config_handler->arrScoreItems;
        $this->arrayLangsByPart = $config_handler->getArrayLangsByPart($date);
        $this->targetPart = [];
        $this->arrayNumLangsByPart = [];
        $this->arrayShiftObjectsByShift = [];
        $this->enoughLangsByPart = [];
        $this->arrBalancesByPart = [];
        // $this->arrShiftStatus = [];
        $this->arrShiftAppObjectsByIdUser = [];
        $this->arrScoresByIdUser = [];
        $this->arrShiftPartStatus = [];
        $this->arrShiftStatus = [];
        $this->setArrShiftPartStatus();
        $this->setArrShiftStatus();
    }

    private function setArrShiftPartStatus()
    {
        for ($shiftPart = 0; $shiftPart < $this->config_handler->numOfShiftsPart; $shiftPart++) {
            echo "shiftPart = $shiftPart <br>";
            $this->arrShiftPartStatus[$shiftPart] = new ShiftPartStatus($shiftPart, $this->date, $this->config_handler);
        }
    }

    private function setArrShiftStatus()
    {
        // ShiftStatus is needed only for shift with numMax.
        foreach ($this->config_handler->arrayShiftsByPart as $shiftPart => $arrShifts) {
            foreach ($arrShifts as $shift) {
                $this->arrShiftStatus[$shift] = new ShiftStatus($this->date, $shift, $shiftPart, $this->config_handler);
            }
        }
    }

    public function pushShiftAppObject($shiftObject)
    {
        if (!isset($this->arrShiftAppObjectsByIdUser[$shiftObject->memberObject->id_user])) {
            $this->arrShiftAppObjectsByIdUser[$shiftObject->memberObject->id_user] = [];
        }
        array_push($this->arrShiftAppObjectsByIdUser[$shiftObject->memberObject->id_user], $shiftObject);
        $this->pushShiftAppObjectToShiftPartStatus($shiftObject);
        $this->pushShiftAppObjectToShiftStatus($shiftObject);
        // echo "Pushing shiftAppObject to DateShiftsDeployer... $shiftObject->date $shiftObject->shift<br>";
        // echo 'Now id_user ' . $shiftObject->memberObject->id_user . ' has ' . count($shiftObject->memberObject->arrShiftAppObjects) . ' shiftAppObjects.<br>';
        // var_dump($shiftObject->memberObjects->arrShiftAppObjects);
        // echo count($shiftObject->memberObject->arrShiftAppObjects);
    }

    private function pushShiftAppObjectToShiftPartStatus($shiftObject)
    {
        // This method is called in pushShiftAppObject method.
        $this->arrShiftPartStatus[$shiftObject->shiftPart]->pushArrShiftAppObjectsByIdUser($shiftObject);
        $this->arrShiftPartStatus[$shiftObject->shiftPart]->updateProps();
    }

    public function pushShiftAppObjectToShiftStatus($shiftObject)
    {
        $this->arrShiftStatus[$shiftObject->shift]->pushArrShiftAppObjectsByIdUser($shiftObject);
        $this->arrShiftStatus[$shiftObject->shift]->updateProps();
    }

    public function deployAllShifts()
    {

        // Update MemberObjects: add 1 to numDaysProceeded
        $this->addNumDaysProceeded();
        // If there is candidates AND not all parts are full
        // echo '$this->arrShiftAppObjectsByIdUser <br>';
        // var_dump($this->arrShiftAppObjectsByIdUser);

        while (count($this->arrShiftAppObjectsByIdUser) && $this->targetPart !== NULL) {
            $this->deployShift();
        }
        // Set enough langs
        echo 'Set enough langs<br>';
        $this->setEnoughLangsByPart(); // DateObject method
    }

    private function deployShift()
    {
        // Set target part
        $this->setTargetPart();
        echo 'HERE $this->targetPart<br>';
        // var_dump($this->targetPart);
        if ($this->targetPart === NULL) {
            // If all parts are full
            return;
        }
        // Calc scores
        $this->setArrScoresByIdUser();

        // Select member and shift
        $id_user_seleted = $this->getMemberToDeploy();
        $shiftObjectDeployed = $this->deployShiftOfMember($id_user_seleted);

        // Unset Member from candidates i.e. Unset from DateShiftsDeployer::arrShiftAppObjects
        unset($this->arrShiftAppObjectsByIdUser[$id_user_seleted]);
        // echo "User $id_user_seleted has been unset.<br>";
        // var_dump($this->arrShiftAppObjectsByIdUser[$id_user_seleted]);
        // echo '<br>';
        // var_dump(array_keys($this->arrShiftAppObjectsByIdUser));
        // echo '<br>';

        if ($shiftObjectDeployed === false) {
            // No valid shift found for this member.
            return;
        } else {
            $currentVac = $this->arrShiftStatus[$shiftObjectDeployed->shift]->vacancy;
            echo "Shift deployed: $shiftObjectDeployed->shift, current shift status vacancy: $currentVac<br>";
            // Push to arrayShiftObjectsByShift
            $this->pushArrayShiftObjectByShift($shiftObjectDeployed); // DateObject method
            $this->pushArrayNumLangsByPart($shiftObjectDeployed); // DateObject method: update numLangs
            // echo 'keys of arrShiftStatus<br>';
            // var_dump($this->arrShiftStatus);
            // echo '<br>';

            // Push to ShiftPartStatus
            $this->arrShiftPartStatus[$shiftObjectDeployed->shiftPart]->pushArrShiftObjectByIdUser($shiftObjectDeployed); // For ShiftPartStatus
            // Update props for ShiftPartStatus
            $this->arrShiftPartStatus[$shiftObjectDeployed->shiftPart]->updateProps();
            // Unset from ShiftPartStatus::arrShiftAppObjectsByIdUser. For DateShiftsDeployer, already done above.
            $this->arrShiftPartStatus[$shiftObjectDeployed->shiftPart]->unsetShiftAppObjectsByIdUser($shiftObjectDeployed);

            // Push to ShiftStatus
            $this->arrShiftStatus[$shiftObjectDeployed->shift]->pushArrShiftObjectByIdUser($shiftObjectDeployed);
            // Update props for ShiftStatus
            $this->arrShiftStatus[$shiftObjectDeployed->shift]->updateProps();
            // Unset from ShiftStatus::$arrShiftAppObjectsByIdUser.
            $this->arrShiftStatus[$shiftObjectDeployed->shift]->unsetShiftAppObjectsByIdUser($shiftObjectDeployed);

            // Update prop of MemberObject;
            $shiftObjectDeployed->memberObject->updateProps();

            // If this shift part is full, unset rest of ShiftAppObjects for this shift part from DateShiftsDeployer
            // 
            if ($this->arrShiftPartStatus[$shiftObjectDeployed->shiftPart]->vacancy === 1) {
                foreach ($this->arrShiftAppObjectsByIdUser as $id_user => $arrShiftAppObjects) {
                    foreach ($this->arrShiftPartStatus[$shiftObjectDeployed->shiftPart]->arrShiftAppObjectsByIdUser as $id_user => $arrShiftAppObjectsPartStatus) {
                        foreach (array_keys(utils\array_intersect_objects($arrShiftAppObjects, $arrShiftAppObjectsPartStatus)) as $key) {
                            unset($arrShiftAppObjects[$key]);
                        };
                    }
                }
                // foreach ($this->arrShiftAppObjectsByIdUser as $id_user => $arrShiftAppObjects) {
                //     foreach ($arrShiftAppObjects as $key => $shiftObject) {
                //         if ($shiftObject->shiftPart === $shiftObjectDeployed->shiftPart) {
                //             unset($arrShiftAppObjects[$key]);
                //         }
                //     }
                // }
            }
        }
    }

    public function assignAllShifts(ShiftsDistributor $shifts_distributor)
    {
        if (count($this->arrayShiftObjectsByShift)) {
            $arrValues = [];
            foreach ($this->arrayShiftObjectsByShift as $shift => $arrShiftObjects) {
                foreach ($arrShiftObjects as $shiftObject) {
                    array_push($arrValues, "($shiftObject->id_user, '$shiftObject->date_shift', '$shiftObject->shift')");
                }
            }
            $SQLS = "INSERT INTO shifts_assigned (id_user, date_shift, shift) VALUES " . implode(', ', $arrValues) . ';';
            echo "SQLS = $SQLS<br>";
            $shifts_distributor->executeSql($SQLS);
            // $stmt = $shifts_distributor->querySql($SQLS);
            // echo "Now assigning all shifts of $this->date <br>";
            // var_dump($stmt->errorInfo());
            // echo '<br>';
        }
    }

    private function deployShiftOfMember($id_user_seleted)
    {
        // Select Part.
        $filteredValues = $this->pickPartAndShiftObjects($id_user_seleted); // [$part, $arrShiftObjectsFiltered] OR false

        // $arrShiftObjectsOfPart = [];
        // foreach ($this->arrShiftAppObjectsByIdUser[$id_user_seleted] as $key => $shiftObject){
        //     if ($shiftObject->shiftPart === $this->targetPart){
        //         $arrShiftObjectsOfPart[$key] = $shiftObject;
        //     }
        // }

        // var_dump($filteredValues);
        // echo '<br>';

        // Select a Shift in the part
        if ($filteredValues === false) {
            return false;
        } else {
            // Here we consider ShiftPartStatus->vacancy === 1;
            $shiftObjectDeployed = $this->decideShift($filteredValues[1]);
            return $shiftObjectDeployed;
        }
    }

    private function pickPartAndShiftObjects($id_user_seleted)
    {
        echo "Deploying Shift of id_user = $id_user_seleted <br>";
        // if count shift = 1
        if (count($this->arrShiftAppObjectsByIdUser[$id_user_seleted]) === 1) {
            echo 'User applied for only one shift.<br>';
            if ($this->arrShiftPartStatus[$this->arrShiftAppObjectsByIdUser[$id_user_seleted][0]->shiftPart]->vacancy >= 1) {
                echo 'This shift part is already full.<br>';
                echo 'Vacancy = ' . $this->arrShiftPartStatus[$this->arrShiftAppObjectsByIdUser[$id_user_seleted][0]->shiftPart]->vacancy . '<br>';
                // Unset this shiftApp from DateShiftsDeployer::arrShiftAppObjectsByIdUser and ShiftStatus::arrShiftAppObjectsByIdUser. This can no longer be used.
                $this->unsetShiftAppObject($this->arrShiftAppObjectsByIdUser[$id_user_seleted][0]);
                // This member is out.
                return false;
            } else {
                echo 'Part and Shift decided!<br>';
                $shiftPart = $this->arrShiftAppObjectsByIdUser[$id_user_seleted][0]->shiftPart;
                $arrShiftObjectsFiltered = $this->arrShiftAppObjectsByIdUser[$id_user_seleted];
                // var_dump($arrShiftObjectsFiltered);
            }
        } else {
            echo 'Check splited part.<br>';
            // check splited part?
            $arrKeyPartsApp = [];
            $arrShiftAppObjectsByPart = [];
            foreach ($this->arrShiftAppObjectsByIdUser[$id_user_seleted] as $shiftObject) {
                // Check if this shift part is already filled out.
                if ($this->arrShiftPartStatus[$shiftObject->shiftPart]->vacancy >= 1) {
                    echo 'This shift PART is already full.<br>';
                    // Unset this shiftApp from DateShiftsDeployer::arrShiftAppObjectsByIdUser and ShiftStatus::arrShiftAppObjectsByIdUser. This can no longer be used.
                    $this->unsetShiftAppObject($shiftObject);
                    // Search for next shift
                    continue;
                }
                // Check if this shift is already filled out.
                if ($this->arrShiftStatus[$shiftObject->shift]->vacancy >= 1) {
                    echo 'This shift is already full.<br>';
                    // Unset this shiftApp from DateShiftsDeployer::arrShiftAppObjectsByIdUser and ShiftStatus::arrShiftAppObjectsByIdUser. This can no longer be used.
                    $this->unsetShiftAppObject($shiftObject);
                    // Search for next shift
                    continue;
                }
                $arrKeyPartsApp[$shiftObject->shiftPart] = NULL;
                if (!isset($arrShiftAppObjectsByPart[$shiftObject->shiftPart])) {
                    $arrShiftAppObjectsByPart[$shiftObject->shiftPart] = [];
                }
                array_push($arrShiftAppObjectsByPart[$shiftObject->shiftPart], $shiftObject);
            }
            echo "Count of part candidates: " . count($arrKeyPartsApp) . '<br>';
            if (count($arrKeyPartsApp) === 0) {
                // This member is out.
                return false;
            } elseif (count($arrKeyPartsApp) === 1) {
                $shiftPart = array_keys($arrKeyPartsApp)[0];
                $arrShiftObjectsFiltered = $this->arrShiftAppObjectsByIdUser[$id_user_seleted];
            } else {
                echo 'Multiple part candidates.<br>';
                // Splited.
                // Select part according to lang contribution
                // Compare in order of lang priority in each part
                $arrLingualities = [];
                foreach ($this->config_handler->arrayLangsShort as $lang) {
                    if ($this->arrayMemberObjectsByIdUser[$id_user_seleted]->$lang === '1') {
                        array_push($arrLingualities, $lang);
                    }
                }
                // $i: i th priority of language
                for ($i = 0; $i < $this->config_handler->numLangs; $i++) {
                    $arrLangVacancyByPart = [];
                    foreach (array_keys($arrKeyPartsApp) as $shiftPart) {
                        $arrLangs = $this->arrShiftPartStatus[$shiftPart]->arrLangs;
                        $lang = array_keys($arrLangs)[$i];
                        if (!in_array($lang, $arrLingualities) || $arrLangs[$lang] === NULL) {
                            // Search for next part
                            continue;
                        }
                        if (!isset($this->arrayNumLangsByPart[$shiftPart][$lang])) {
                            $arrLangVacancyByPart[$shiftPart] = 0;
                        } else {
                            $arrLangVacancyByPart[$shiftPart] = $this->arrayNumLangsByPart[$shiftPart][$lang] / $arrLangs[$lang];
                        }
                    }
                    if (count($arrLangVacancyByPart)) {
                        if (min($arrLangVacancyByPart) < 1) {
                            // Insufficient
                            $shiftPart = array_keys($arrLangVacancyByPart, min($arrLangVacancyByPart));
                            if (is_array($shiftPart)) {
                                // part selected: $shiftPart
                                $shiftPart = $shiftPart[mt_rand(0, count($shiftPart) - 1)];
                            }
                            break;
                        }
                    }
                    // This will give randomly selected part if not decided until last priority
                    $shiftPart = array_keys($arrKeyPartsApp)[mt_rand(0, count($arrKeyPartsApp) - 1)];
                    // If not break, search for next priority
                }
            }
            $arrShiftObjectsFiltered = $arrShiftAppObjectsByPart[$shiftPart];
        }
        echo "Selected part: $shiftPart";
        return [$shiftPart, $arrShiftObjectsFiltered];
    }

    private function getVacantShiftPartStatus()
    {
        $arrShiftPartStatusVacant = $this->arrShiftPartStatus;
        foreach ($arrShiftPartStatusVacant as $shiftPart => $shiftPartStatus) {
            if ($shiftPartStatus->vacancy === 1 || $shiftPartStatus->vacancy > 1) {
                unset($arrShiftPartStatusVacant[$shiftPart]);
            }
        }
        // if(!count($arrShiftPartStatusVacant)){
        // echo "All shift parts are full: No options to decide shift.<br>";
        // return false;
        // }
        return $arrShiftPartStatusVacant;
    }

    private function getVacantShiftStatus()
    {
        $arrShiftStatusVacant = $this->arrShiftStatus;
        foreach ($arrShiftStatusVacant as $shift => $shiftStatus) {
            if ($shiftStatus->vacancy === 1 || $shiftStatus->vacancy > 1) {
                $hasShift = in_array($shift, array_keys($arrShiftStatusVacant));
                // echo "arrShiftStatusVacant has $shift: ";
                // var_dump($hasShift);
                // echo "Shift $shift is already full: Vacancy = $shiftStatus->vacancy<br>";

                unset($arrShiftStatusVacant[$shift]);

                // $hasShift = in_array($shift, array_keys($arrShiftStatusVacant));
                // echo "arrShiftStatusVacant has $shift: ";
                // var_dump($hasShift);
                // echo '<br>';
            }
        }
        echo 'num of vacant shift status: ' . count($arrShiftStatusVacant) . '<br>';
        return $arrShiftStatusVacant;
    }

    private function decideShift($arrShiftObjectsFiltered)
    {
        // Get vacant ShiftPartStatus
        $arrShiftPartStatusVacant = $this->getVacantShiftPartStatus();
        // Filter $arrShiftObjectsFiltered
        foreach ($arrShiftObjectsFiltered as $key => $shiftObject) {
            if (!array_key_exists($shiftObject->shiftPart, $arrShiftPartStatusVacant)) {
                unset($arrShiftObjectsFiltered[$key]);
            }
        }
        // Get vacant ShiftStatus
        $arrShiftStatusVacant = $this->getVacantShiftStatus();
        // Filter $arrShiftObjectsFiltered
        foreach ($arrShiftObjectsFiltered as $key => $shiftObject) {
            if (!array_key_exists($shiftObject->shift, $arrShiftStatusVacant)) {
                unset($arrShiftObjectsFiltered[$key]);
            }
        }

        if (count($arrShiftObjectsFiltered) === 0) {
            echo 'All shifts have been filtered and no valid shift found.<br>';
            return false;
        } elseif (count($arrShiftObjectsFiltered) === 1) {
            echo 'One shift passed. No sorting needed.<br>';
        } else {
            // echo '<br>ShiftPriority<br>';
            // echo 'Before sort:<br>';
            // var_dump(array_keys($this->arrShiftStatus));
            // echo '<br>';
            echo 'Getting shift part priority<br>';
            uasort($arrShiftPartStatusVacant, function ($a, $b) {
                if ($a->percentApp == $b->percentApp) {
                    return 0;
                }
                return ($a->percentApp < $b->percentApp) ? -1 : 1;
            });
            echo 'After sort:<br>';
            foreach ($arrShiftPartStatusVacant as $shiftPartStatus) {
                echo "$shiftPartStatus->shiftPart $shiftPartStatus->percentApp<br>";
            }
            echo '<br>';
            $shiftPartPriority = array_keys($arrShiftPartStatusVacant);

            // Shift prioirity
            echo 'Getting shift priority by ratioMin<br>';
            uasort($arrShiftStatusVacant, function ($a, $b) {
                if ($a->ratioMin == $b->ratioMin) {
                    return 0;
                }
                return ($a->ratioMin < $b->ratioMin) ? -1 : 1;
            });
            echo 'After sort:<br>';
            foreach ($arrShiftStatusVacant as $shiftStatus) {
                echo "$shiftStatus->shift $shiftStatus->ratioMin<br>";
            }
            echo '<br>';
            $shiftPriority = array_keys($arrShiftStatusVacant);
            // echo '$arrShiftObjectsFiltered';
            // echo 'Before sort:<br>';
            // var_dump($arrShiftObjectsFiltered);
            // echo '<br>';

            shuffle($arrShiftObjectsFiltered);
            // Sort by Part priority then Shift prioirity
            usort($arrShiftObjectsFiltered, function ($a, $b) use ($shiftPartPriority, $shiftPriority) {
                $pos_part_a = array_search($a->shiftPart, $shiftPartPriority);
                $pos_part_b = array_search($b->shiftPart, $shiftPartPriority);
                if ($pos_part_a === $pos_part_b) {
                    $pos_shift_a = array_search($a->shift, $shiftPriority);
                    $pos_shift_b = array_search($b->shift, $shiftPriority);
                    return $pos_shift_a - $pos_shift_b;
                }
                return $pos_part_a - $pos_part_b;
            });
        }
        return $arrShiftObjectsFiltered[0];
    }

    private function unsetShiftAppObject($shiftObjectDeployed)
    {
        // For ShiftsDistributor: No way to access 
        // For DateShiftsDeployer
        $this->unsetShiftAppObjectsByIdUser($shiftObjectDeployed);
        // For ShiftStatus
        $this->arrShiftPartStatus[$shiftObjectDeployed->shiftPart]->unsetShiftAppObjectsByIdUser($shiftObjectDeployed);
    }

    private function unsetShiftAppObjectsByIdUser($shiftObject)
    {
        $key = array_search($shiftObject, $this->arrShiftAppObjectsByIdUser[$shiftObject->id_user], true);
        if ($key !== false) {
            unset($this->arrShiftAppObjectsByIdUser[$shiftObject->id_user][$key]);
        } else {
            echo 'ERROR! Shift object being deployed has already been unset from (or never been pushed into) DateShiftsDeployer::arrShiftAppObjectsByIdUser.';
            exit;
        }
    }

    public function getMemberToDeploy()
    {
        // Call after setting arrScoresByIdUser
        foreach ($this->arrScoreItems as $score_item_name => $minORmax) {
            if (count($this->arrScoreItems) > 1) {
                // Every step, $this->arrScoresByIdUser is shrinked.
                $this->genAndFilterArrScoresByIdUser($score_item_name, $minORmax);
            } else {
                break;
            }
        }
        echo 'Filtering completed: <br>';
        if (count($this->arrScoresByIdUser) > 1) {
            $id_user_seleted = array_keys($this->arrScoresByIdUser)[mt_rand(0, count($this->arrScoresByIdUser) - 1)];
        } else {
            $id_user_seleted = array_keys($this->arrScoresByIdUser)[0];
        }
        echo "id_user_seleted: $id_user_seleted";
        echo '<br>';
        return $id_user_seleted;
    }

    private function genAndFilterArrScoresByIdUser(string $score_item_name, string $minORmax)
    {
        $arrItemValues = [];
        foreach ($this->arrScoresByIdUser as $id_user => $arrScores) {
            $arrItemValues[$id_user] = $arrScores[$score_item_name];
        }
        var_dump($arrItemValues);
        echo '<br>';
        echo "Now selecting $minORmax $score_item_name <br>";
        // var_dump($arrItemValues);
        if ($minORmax === 'max') {
            $threshold = max($arrItemValues);
        } elseif ($minORmax === 'min') {
            $threshold = min($arrItemValues);
        } else {
            echo 'Argument not understood.';
            exit;
        }
        echo "Threshold: " . $threshold . '<br>';
        $arrItemValues = array_filter($arrItemValues, function ($val) use ($threshold) {
            return $val === $threshold;
        });
        $newArrScoresByIdUser = [];
        foreach (array_keys($arrItemValues) as $id_user) {
            $newArrScoresByIdUser[$id_user] = $this->arrScoresByIdUser[$id_user];
        }
        $this->arrScoresByIdUser = $newArrScoresByIdUser;
    }

    public function setArrScoresByIdUser()
    {
        // echo '2nd call of setArrScoresByIdUser<br>';
        // var_dump(array_keys($this->arrShiftAppObjectsByIdUser));
        // echo '<br>';
        echo 'Num of Candidates: ' . count($this->arrShiftAppObjectsByIdUser) . '<br>';
        // Init arrScoresByIdUser
        $this->arrScoresByIdUser = [];

        // echo "In this target part, following members have applied:<br>";
        // var_dump(array_keys($this->arrShiftPartStatus[$this->targetPart]->arrShiftAppObjectsByIdUser));
        // echo '<br>';
        foreach (array_keys($this->arrShiftAppObjectsByIdUser) as $id_user) {
            $this->setArrScores($id_user);
            // var_dump($this->arrScoresByIdUser);
            // echo '<br>';
        }
    }

    public function setArrScores($id_user)
    {
        $this->setAppForTargetPart($id_user);
        $this->setNumShiftAppObjects($id_user);
        $this->setNumAppNotEnough($id_user);
        $this->setLangScore($id_user);
        $this->setDeployRatio($id_user);
    }

    private function setAppForTargetPart($id_user)
    {
        // echo "Target part's members:<br>";
        // var_dump(array_keys($this->arrShiftPartStatus[$this->targetPart]->arrShiftAppObjectsByIdUser));
        // echo '<br>';
        $this->arrScoresByIdUser[$id_user]['appForTargetPart'] = (in_array($id_user, array_keys($this->arrShiftPartStatus[$this->targetPart]->arrShiftAppObjectsByIdUser))) ? 1 : 0;
    }

    private function setTargetPart()
    {
        echo 'Now setting Target Part... <br>';
        $this->targetPart = [];
        $vacancyLowest = 1;
        foreach ($this->arrShiftPartStatus as $shiftPart => $shiftPartStatus) {
            echo "Part $shiftPart's vacancy: $shiftPartStatus->vacancy and lowest vacancy was $vacancyLowest<br>";
            if ($shiftPartStatus->vacancy === 1) {
                continue;
            } elseif ($shiftPartStatus->vacancy < $vacancyLowest) {
                $vacancyLowest = $shiftPartStatus->vacancy;
                $this->targetPart = [$shiftPart];
            } elseif ($shiftPartStatus->vacancy === $vacancyLowest) {
                $this->targetPart[] = $shiftPart;
            }
        }
        if (count($this->targetPart)) {
            $this->targetPart = $this->targetPart[mt_rand(0, count($this->targetPart) - 1)];
            echo "Target part: $this->targetPart<br>";
        } else {
            echo 'All shift parts are full. Setting $this->targetPart = NULL. <br>';
            $this->targetPart = NULL;
        }
    }

    private function setNumShiftAppObjects($id_user)
    {
        $this->arrScoresByIdUser[$id_user]['numShiftAppObjects'] = count($this->arrShiftAppObjectsByIdUser[$id_user]);
    }

    private function setNumAppNotEnough($id_user)
    {
        $this->arrScoresByIdUser[$id_user]['numAppNotEnough'] = 0;
        foreach ($this->arrShiftAppObjectsByIdUser[$id_user] as $shiftObject) {
            // echo "$this->date $shiftObject->shift" . ' Percentage:' . $this->arrShiftStatus[$shiftObject->shift]->vacancy . '<br>';
            if ($this->arrShiftStatus[$shiftObject->shift]->ratioMin < 1) {
                $this->arrScoresByIdUser[$id_user]['numAppNotEnough']++;
            }
        }
    }

    private function setDeployRatio($id_user)
    {
        if ($this->arrayMemberObjectsByIdUser[$id_user]->numDaysProceeded === 0) {
            $this->arrScoresByIdUser[$id_user]['deployRatio'] = INF;
        } else {
            $this->arrScoresByIdUser[$id_user]['deployRatio'] = $this->arrayMemberObjectsByIdUser[$id_user]->deployRatio;
        }
    }

    private function setLangScore($id_user)
    {
        $this->arrScoresByIdUser[$id_user]['langScore'] = 0;
        $arr = [];
        foreach ($this->arrShiftAppObjectsByIdUser[$id_user] as $shiftObject) {
            foreach ($this->config_handler->arrayLangsShort as $lang) {
                // For every lang
                if (!isset($arr[$shiftObject->shiftPart][$lang])) {
                    // If the lang in this part wasn't considered
                    if ($this->arrShiftPartStatus[$shiftObject->shiftPart]->arrLangs[$lang] !== NULL) {
                        // If the lang is required in this part
                        if (isset($this->arrayNumLangsByPart[$shiftObject->shiftPart][$lang])) {
                            // If there is already some people who can speak lang in this part
                            if ($this->arrayNumLangsByPart[$shiftObject->shiftPart][$lang] < $this->arrShiftPartStatus[$shiftObject->shiftPart]->arrLangs[$lang]) {
                                // If not enough num
                                if ($shiftObject->memberObject->$lang === '1') {
                                    // If member can speak lang
                                    $arr[$shiftObject->shiftPart][$lang] = true;
                                } else {
                                    $arr[$shiftObject->shiftPart][$lang] = false;
                                }
                            }
                        } else {
                            if ($shiftObject->memberObject->$lang === '1') {
                                // If member can speak lang
                                $arr[$shiftObject->shiftPart][$lang] = true;
                            } else {
                                $arr[$shiftObject->shiftPart][$lang] = false;
                            }
                        }
                    }
                }
            }
        }
        // Like $arr = [['cn' => true], ['cn' => true, 'de' => false, 'kr' => true]];

        $langScore = 0;
        if (count($arr)) {
            foreach ($arr as $subArr) {
                $j = 0;
                foreach ($subArr as $good) {
                    if ($good) {
                        $j++;
                    }
                }
                $langScore += $j / count($subArr);
            }
            $langScore /= count($arr);
            // Like (1/1 + 2/3) / 2
        }
        // $arr could be empty. e.g. all required langs have already been fulfilled.

        $this->arrScoresByIdUser[$id_user]['langScore'] = $langScore;
    }

    public function pushArrNumLangsAppByPart($shiftObject)
    {
        // echo $shiftObject->date . '<br>';
        if (!isset($this->arrNumLangsAppByPart[$shiftObject->shiftPart])) {
            $this->arrNumLangsAppByPart[$shiftObject->shiftPart] = [];
        }
        foreach ($this->config_handler->arrayLangsShort as $lang) {
            if (!isset($this->arrNumLangsAppByPart[$shiftObject->shiftPart][$lang])) {
                $this->arrNumLangsAppByPart[$shiftObject->shiftPart][$lang] = 0;
            }
            // $mem = $shiftObject->memberObject;
            // echo "$shiftObject->shift / $mem->id_user / $lang = " . $shiftObject->memberObject->$lang .'<br>';
            $this->arrNumLangsAppByPart[$shiftObject->shiftPart][$lang] += $shiftObject->memberObject->$lang;
        }
    }

    private function setArrNumLangsAppByPart()
    {
        if (count($this->arrShiftStatus)) {
            // Initialize array
            $this->arrNumLangsAppByPart = [];
            // var_dump($this->arrShiftStatus);
            foreach ($this->arrShiftStatus as $shiftStatus) {
                // echo $shiftObject->date . '<br>';
                // echo '$arrShiftObjects = ';
                // var_dump($arrShiftObjects);
                // echo '<br>';
                foreach ($shiftStatus->arrShiftAppObjectsByIdUser as $arrShiftObjects) {
                    foreach ($arrShiftObjects as $shiftObject) {
                        // var_dump($shiftObject);
                        // echo '<br>';
                        $this->pushArrNumLangsAppByPart($shiftObject);
                    }
                }
            }
            ksort($this->arrNumLangsAppByPart);
        }
    }

    private function addNumDaysProceeded()
    {
        foreach (array_keys($this->arrShiftAppObjectsByIdUser) as $id_user) {
            $this->arrayMemberObjectsByIdUser[$id_user]->addNumDaysProceeded();
        }
    }

    public function getStatistics()
    {
        // var_dump($this->arrayNumLangsByPart);
        echo "<br>Statistics: $this->date<br>";
        foreach ($this->arrShiftPartStatus as $shiftPart => $shiftPartStatus) {
            echo "[Shift PART $shiftPart]<br>";
            // Vacancy of every part
            $style = $this->getStyleTextColor($shiftPartStatus->vacancy, 1);
            echo "Vacancy: <span $style>$shiftPartStatus->vacancy</span><br>";
            // 欠員数
            $deficiency = $shiftPartStatus->numNeeded - count($shiftPartStatus->arrShiftObjectsByIdUser);
            $style = $this->getStyleTextColor($deficiency, 0);
            echo "Num of deficiency: <span $style>$deficiency</span><br>";
            // Langs
            echo 'Language Balances:<br>';
            var_dump($this->arrBalancesByPart);
            echo '<br>';
            foreach ($this->arrBalancesByPart[$shiftPart] as $lang => $balance) {
                $style = $this->getStyleTextColor($balance, 0);
                echo "$lang: <span $style>$balance</span>";
            }
            echo '<br>';
        }
        return [$this->arrShiftPartStatus, $this->arrBalancesByPart];
    }

    private function getStyleTextColor($value, $threshold)
    {
        $styleTextColor = ($value < $threshold) ? 'style="color: red"' : '';
        return $styleTextColor;
    }
}
