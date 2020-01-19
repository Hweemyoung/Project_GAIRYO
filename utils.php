<?php
namespace utils;
$homedir = $homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/config.php";

function customVarDump(string $varName)
{
    echo ($varName) . PHP_EOL;
    global $$varName;
    var_dump($$varName) . PHP_EOL;
}

function groupArrayByKey($array, $key)
{
    $arrayGrouped = array();
    foreach ($array as $element) {

        $arrayGrouped[$element[$key]][] = $element;
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
