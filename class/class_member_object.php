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
        $this->arrShiftAppObjects = [];
        $this->numDaysApplied = 0;
        $this->numDaysProceeded = 0;
        $this->numDaysDeployed = 0;
        $this->deployRatio = 0;
    }
    
    public function pushShiftAppObjects($shiftObject)
    {
        array_push($this->arrShiftAppObjects, $shiftObject);
    }

    public function addNumDaysProceeded(){
        $this->numDaysProceeded++;
        $this->updateDeployRatio();
    }

    public function addNumDaysDeployed(){
        $this->numDaysDeployed++;
        $this->updateDeployRatio();
    }

    private function updateDeployRatio(){
        if ($this->numDaysDeployed === $this->numDaysProceeded) {
            $this->deployRatio = 1;
        } else {
            $this->deployRatio = $this->numDaysDeployed / $this->numDaysProceeded;
        }
    }
}
