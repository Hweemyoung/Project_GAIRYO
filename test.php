<?php
class TempClass{
    public $prop1;
}

$temp1 = new TempClass();
$temp2 = new TempClass();
$temp3 = new TempClass();

var_dump($temp1->prop1);
