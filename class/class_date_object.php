<?php
class DateObject
{
    public $date;
    public static $arrayLangs = ['cn', 'kr', 'th', 'my', 'ru', 'fr', 'de'];
    public $arrayNumLangs;
    public $arrayShiftObjectsByShift;
    function __construct($date, $arrayShiftObjectsOfDate)
    {
        $this->date = $date;
        $this->arrayNumLangs = [];
        $this->arrayShiftObjectsByShift = [];
        $this->setArrayShiftObjectByShift($arrayShiftObjectsOfDate);
        $this->setArrayLangs($arrayShiftObjectsOfDate);
    }
    private function setArrayLangs($arrayShiftObjectsOfDate)
    {
        foreach ($arrayShiftObjectsOfDate as $shiftObject) {
            foreach (self::$arrayLangs as $lang) {
                if ($shiftObject->memberObject->$lang) {
                    if (isset($this->arrayNumLangs[$lang])) {
                        $this->arrayNumLangs[$lang]++;
                    } else {
                        $this->arrayNumLangs[$lang] = 1;
                    }
                }
            }
        }
    }

    private function setArrayShiftObjectByShift($arrayShiftObjectsOfDate)
    {
        foreach ($arrayShiftObjectsOfDate as $shiftObject) {
            $this->arrayShiftObjectsByShift[$shiftObject->shift][] = $shiftObject;
        }
    }
}

class ShiftObject
{
    public $memberObject;
    function __construct()
    {
    }
    public function setMemberObj($arrayMemberObjectsByIdUser)
    {
        $this->memberObject = $arrayMemberObjectsByIdUser[$this->id_user];
    }
}

class MemberObject
{
}