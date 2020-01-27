<?php
namespace utils;

use Exception;

$homedir = $homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/config.php";

function customVarDump(string $varName)
{
    echo ($varName) . PHP_EOL;
    global $$varName;
    var_dump($$varName) . PHP_EOL;
}

function randFloat(){
    return mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax();
}

function groupArrayByValue($array, $key)
{
    $arrayGrouped = array();
    foreach ($array as $element) {
        switch (gettype($element)){
            case 'array':
                $arrayGrouped[$element[$key]][] = $element;
                break;
            case 'object':
                $arrayGrouped[$element->$key][] = $element;
                break;
            default:
                throw new Exception("Date type inappropriate");
        }
    }
    return $arrayGrouped;
}

function genHref(string $http_host, string $url, array $query){
    // $http_host = 'localhost' , $url = 'gairyo_temp/*.php'
    if (count($query)){
        for($i=0;$i<count($query);$i++){
            if ($i === 0){
                $seperator = '?';
            } else {
                $seperator = '&';
            }
            $url = $url . $seperator . array_keys($query)[$i] . '=' . $query[array_keys($query)[$i]];
        }
    }
    return $http_host . '/' . $url;
}

function isAssoc(array $arr)
{
    if (array() === $arr) return 2;
    return array_keys($arr) !== range(0, count($arr) - 1);
}

function getClassTextColorForDay($day){
    switch ($day){
        case 'Sun':
            $classTextColor = 'text-danger';
            break;
        case 'Sat':
            $classTextColor = 'text-primary';
            break;
        default:
            $classTextColor = '';
    }
    return $classTextColor;
}