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
        $this->numDaysDeployed = 0;
    }
    
    public function pushShiftAppObjects($shiftObject)
    {
        array_push($this->arrShiftAppObjects, $shiftObject);
    }
}
