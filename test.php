<?php
class O{
    private $prop = 'asd';
    private $sql;
    function sql(){
        $this->sql = "$this->prop";
        var_dump($this->sql);
    }
}

$inst = new O();
$inst->sql();
?>