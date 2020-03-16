<?php
class MemberObject
{
    // public function initAppliedByPart()
    // {
    // Used in ShiftsDistributor
    // $this->appliedByPart = [];
    // }


    public function initProps()
    {
        // $this->arrShiftObjectsByDate = [];
        // $this->arrShiftAppObjects = [];
        $this->numDaysApplied = 0;
        $this->numDaysProceeded = 0;
        $this->numDaysDeployed = 0;
        $this->arrWorkedDatesByWeek = [];
        $this->workedMinsTotal = 0;
        $this->workedMinsByWeek = [];
        $this->arrWorkedMinsByDate = [];
        $this->deployRatio = 0;
    }

    // public function pushShiftAppObjects($shiftObject)
    // {
    //     array_push($this->arrShiftAppObjects, $shiftObject);
    // }

    public function addNumDaysProceeded()
    {
        $this->numDaysProceeded++;
        $this->updateDeployRatio();
    }

    public function updateProps($shiftObjectDeployed, $lastDayOfMonth)
    {
        $this->numDaysDeployed++;
        $this->updateDeployRatio();

        $this->pushArrWorkdMinsByDate($shiftObjectDeployed, $lastDayOfMonth);
        $this->workedMinsTotal += $shiftObjectDeployed->workingMins;
        echo "Worked mins total: $this->workedMinsTotal<br>";
        // $this->addWorkedMins($shiftObjectDeployed);
        // $this->pushArrWorkedDates($shiftObjectDeployed);
    }

    private function pushArrWorkdMinsByDate($shiftObjectDeployed, $lastDayOfMonth)
    {
        $currentDateTime = new DateTime($shiftObjectDeployed->date_shift);
        $key = $currentDateTime->format('j');
        $key = ($key > 15) ? $key : $key + $lastDayOfMonth; //array_keys($this->arrWorkedMinsByDate) = [16, ... , 28, 29, 30, ... ,44]
        $this->arrWorkedMinsByDate[$key] = $shiftObjectDeployed->workingMins;
        echo "Now user $this->id_user 's arrWorkedMinsByDate:<br>";
        var_dump($this->arrWorkedMinsByDate);
        echo '<br>';
    }

    private function addWorkedMins($shiftObjectDeployed)
    {
        $currentDateTime = new DateTime($shiftObjectDeployed->date_shift);
        $W = $currentDateTime->format('W');
        if (!isset($this->workedMinsByWeek[$W])) {
            $this->workedMinsByWeek[$W] = 0;
        }
        $this->workedMinsByWeek[$W] += $shiftObjectDeployed->workingMins;
        // echo "Worked mins for week $W: $this->workedMinsByWeek[$W]<br>";
        $this->workedMinsTotal += $shiftObjectDeployed->workingMins;
        echo "Worked mins total: $this->workedMinsTotal<br>";
    }

    private function pushArrWorkedDates($shiftObjectDeployed)
    {
        $currentDateTime = new DateTime($shiftObjectDeployed->date_shift);
        // var_dump($currentDateTime->format('W'));
        // if (!isset($this->workedMinsByWeek[$currentDateTime->format('W')])) {
        // $this->workedMinsByWeek[$currentDateTime->format('W')] = [];
        // }
        $this->arrWorkedDatesByWeek[$currentDateTime->format('W')][] = $shiftObjectDeployed->date_shift;
    }

    private function updateDeployRatio()
    {
        if ($this->numDaysDeployed === $this->numDaysProceeded) {
            $this->deployRatio = 1;
        } else {
            $this->deployRatio = $this->numDaysDeployed / $this->numDaysProceeded;
        }
    }
}
