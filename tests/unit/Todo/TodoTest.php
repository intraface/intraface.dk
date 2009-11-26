<?php

require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

require_once 'Intraface/Kernel.php';
require_once 'Intraface/DBQuery.php';
require_once 'Intraface/modules/todo/TodoList.php';

error_reporting(E_ALL);

class TodoTest extends PHPUnit_Framework_TestCase
{
    private $todo;

    function setUp()
    {
        $this->todo = new TodoList(new Stub_Kernel);
    }

    function testConstruction()
    {
        $this->assertTrue(is_object($this->todo));
    }

}