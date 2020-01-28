<?php
class TempClass{}

$temp1 = new TempClass();
$temp2 = new TempClass();
$temp3 = new TempClass();

$arr1 = [$temp1, $temp2];
$arr2 = [$temp2, $temp3];

var_dump(array_uintersect($arr1, $arr2, function($arr1, $arr2) {
    return strcmp(spl_object_hash($arr1), spl_object_hash($arr2));
}));