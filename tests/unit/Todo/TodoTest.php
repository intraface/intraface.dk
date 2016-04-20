<?php
require_once 'Intraface/Kernel.php';
require_once 'Intraface/DBQuery.php';
require_once 'Intraface/modules/todo/TodoList.php';

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
