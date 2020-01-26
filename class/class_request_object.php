<?php
class RequestObject
{
    public function __construct()
    {
        $this->setMode();
    }

    public function setMode()
    {
        // 0: Normal 1: Put 2: Call
        if($this->id_from !== NULL && $this->id_to !== NULL){
            $this->mode = 0;
        } elseif($this->id_from === NULL && $this->id_to == NULL){
            echo 'Error: Cannot identify mode. Exit!';
            exit;
        } elseif($this->id_to == NULL){
            $this->mode = 1;
        } elseif($this->id_from === NULL){
            $this->mode = 2;
        }
    }
}
