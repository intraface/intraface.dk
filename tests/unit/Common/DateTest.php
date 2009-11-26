<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';
require_once 'Intraface/Date.php';

date_default_timezone_set('Europe/Berlin');

class DateTest extends PHPUnit_Framework_TestCase
{
    function testDateIsConvertedToDatabaseFormat()
    {
        $date = new Intraface_Date('10-10-2008');
        $this->assertTrue($date->convert2db());
        $this->assertEquals('2008-10-10', $date->get());
    }

    function testDateCanAutomaticallyTakeYearIsConvertedToDatabaseFormat()
    {
        $date = new Intraface_Date('10-10');
        $this->assertTrue($date->convert2db());
        $this->assertEquals(date('Y') . '-10-10', $date->get());
    }

    function testDateCanTakeSpacesAsSplittersDatabaseFormat()
    {
        $date = new Intraface_Date('10 10 2009');
        $this->assertTrue($date->convert2db());
        $this->assertEquals('2009-10-10', $date->get());
    }

    function testDateCanTakeSlashesAsSplittersDatabaseFormat()
    {
        $date = new Intraface_Date('10/10/2009');
        $this->assertTrue($date->convert2db());
        $this->assertEquals('2009-10-10', $date->get());
    }
}