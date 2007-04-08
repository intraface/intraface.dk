<?php
class Registry {
    var $_cache_stack;

    function Registry() {
        $this->_cache_stack = array(array());
    }
    function addEntry($key, &$item) {
        $this->_cache_stack[0][$key] = &$item;
    }
    function &getEntry($key) {
        return $this->_cache_stack[0][$key];
    }
    function isEntry($key) {
        return ($this->getEntry($key) !== null);
    }
    function &instance() {
        static $registry = false;
        if (!$registry) {
            $registry = new Registry();
        }
        return $registry;
    }
    function save() {
        array_unshift($this->_cache_stack, array());
        if (!count($this->_cache_stack)) {
            trigger_error('Registry lost');
        }
    }
    function restore() {
        array_shift($this->_cache_stack);
    }
}
?>