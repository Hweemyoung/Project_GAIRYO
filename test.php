<?php
class TempClass{
    public $prop1;
}
$homedir = '/var/www/html/gairyo_temp';
$csv = array_map('str_getcsv', file("$homedir/data/csv/test.csv"));
var_dump($csv);
