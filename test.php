<?php
require './check_session.php';
class Overloading
{
    private $_arrayProps = array();

    public function __set($_prop, $value)
    {
        $this->_arrayProps[$_prop] = $value;
    }

    public function __get($_prop)
    {
        if (array_key_exists($_prop, $this->_arrayProps)) {
            return $this->_arrayProps[$_prop];
        }
        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $_prop .
                ' in ' . $trace[0]['file'] .
                ' on line ' . $trace[0]['line'],
            E_USER_NOTICE
        );
        return null;
    }

    public function __isset($_prop)
    {
        return isset($this->_arrayProps[$_prop]);
    }

    public function __unset($_prop)
    {
        unset($this->_arrayProps[$_prop]);
    }
}

class MemberObject extends Overloading
{
}

class ShiftObject extends Overloading
{
}