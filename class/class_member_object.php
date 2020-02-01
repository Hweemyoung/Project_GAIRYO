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
        $this->deployRatio = 0;
        $this->workedMinsByWeek = [];
        $this->arrWorkedDatesByWeek = [];
    }
    
    // public function pushShiftAppObjects($shiftObject)
    // {
    //     array_push($this->arrShiftAppObjects, $shiftObject);
    // }

    public function addNumDaysProceeded(){
        $this->numDaysProceeded++;
        $this->updateDeployRatio();
    }

    public function updateProps($shiftObjectDeployed){
        $this->numDaysDeployed++;
        $this->updateDeployRatio();
        
        $this->addWorkedMins($shiftObjectDeployed);
        $this->pushArrWorkedDates($shiftObjectDeployed);
    }

    private function addWorkedMins($shiftObjectDeployed){
        $currentDateTime = new DateTime($shiftObjectDeployed->date_shift);
        if (!isset($this->workedMinsByWeek[$currentDateTime->format('W')])){
            $this->workedMinsByWeek[$currentDateTime->format('W')] = 0;
        }
        $this->workedMinsByWeek[$currentDateTime->format('W')] += $shiftObjectDeployed->workingMins;
    }

    private function pushArrWorkedDates($shiftObjectDeployed){
        $currentDateTime = new DateTime($shiftObjectDeployed->date_shift);
        $this->workedMinsByWeek[$currentDateTime->format('W')][] = $shiftObjectDeployed->date_shift;
    }

    private function updateDeployRatio(){
        if ($this->numDaysDeployed === $this->numDaysProceeded) {
            $this->deployRatio = 1;
        } else {
            $this->deployRatio = $this->numDaysDeployed / $this->numDaysProceeded;
        }
    }
}
