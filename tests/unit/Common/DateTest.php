<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';

date_default_timezone_set('Europe/Berlin');

class Common_DateTest extends PHPUnit_Framework_TestCase
{
    function testDate()
    {
        $date = new Intraface_Date('10-10-2008');
        echo $date->convert2db();
    }
}