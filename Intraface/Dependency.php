<?php
class Intraface_Dependency
{
	private $phemto;

    function __construct()
    {
    	require_once 'phemto.php';
        $this->phemto = new Phemto();
    }

    function willUse($name, $params = array())
    {
    	$this->phemto->willUse($name, $params);
    }

    function create($name, $params = array())
    {
    	return $this->phemto->create($name, $params);
    }

    function whenCreating($object_name)
    {
    	return $this->phemto->whenCreating($object_name);
    }

}

