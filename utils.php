<?php
function groupArrayByKey($array, $key)
{
    $arrayGrouped = array();
    foreach ($array as $element) {

        $arrayGrouped[$element[$key]][] = $element;
    }
    return $arrayGrouped;
}
?>