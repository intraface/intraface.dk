<?php
require_once dirname(__FILE__) . './../config.test.php';

require_once 'Intraface/modules/accounting/Year.php';
require_once 'Intraface/functions.php';

class FakeYearSetting
{
    private $setting = array();
    function get($none, $key)
    {
        if (!isset($this->setting[$key])) {
            return '';
        }
        return $this->setting[$key];
    }
    function set($none, $key, $value)
    {
        $this->setting[$key] = $value;
    }
}

class FakeYearIntranet
{
    function get()
    {
        return 1;
    }
}

class FakeYearUser
{
    function get()
    {
        return 1;
    }
}

class FakeYearKernel
{
    public $setting;
    public $intranet;
    public $user;

    function __construct()
    {
        $this->setting = new FakeYearSetting;
        $this->intranet = new FakeYearIntranet;
        $this->user = new FakeYearUser;
    }
}

class YearTest extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $this->kernel = new FakeYearKernel();
    }

    function testSetYearReturnsFalseWhenYearObjectIsNotSet()
    {
        $year = new Year($this->kernel);
        $this->assertFalse($year->setYear());
    }

    function testSetYearReturnsTrueWhenYearObjectIsSet()
    {
        $year = new Year($this->kernel);
        $id = $year->save(array('label' => '2000', 'locked' => 0, 'from_date' => '2000-1-1', 'to_date' => '2000-12-31', 'last_year_id' => 0));
        $this->assertTrue($id > 0);
        $this->assertTrue($year->setYear());
        $this->assertEquals($id, $year->getActiveYear());
    }

    function testCheckYearReturnsTrueIfActiveYearIsset()
    {
        $year = new Year($this->kernel);
        $id = $year->save(array('label' => '2000', 'locked' => 0, 'from_date' => '2000-1-1', 'to_date' => '2000-12-31', 'last_year_id' => 0));
        $this->assertTrue($id > 0);
        $this->assertTrue($year->setYear());
        $this->assertTrue($year->checkYear());
    }

    function testCheckYearReturnsFalseIfActiveYearIsNotset()
    {
        $year = new Year($this->kernel);
        $id = $year->save(array('label' => '2000', 'locked' => 0, 'from_date' => '2000-1-1', 'to_date' => '2000-12-31', 'last_year_id' => 0));
        $this->assertFalse($year->checkYear(false));
    }

    function testIsYearSetReturnsTrueWhenYearIsset()
    {
        $year = new Year($this->kernel);
        $id = $year->save(array('label' => '2000', 'locked' => 0, 'from_date' => '2000-1-1', 'to_date' => '2000-12-31', 'last_year_id' => 0));
        $this->assertTrue($id > 0);
        $this->assertTrue($year->setYear());
        $this->assertTrue($year->isYearSet());
    }

    function testSaveMethod()
    {
        // TODO needs to be updated
        $year = new Year($this->kernel);
        $this->assertFalse($year->get('id') > 0);
        $this->assertTrue($year->save(array('label' => '2000', 'locked' => 0, 'from_date' => '2000-1-1', 'to_date' => '2000-12-31', 'last_year_id' => 0)) > 0);
        $this->assertEquals('2000', $year->get('label'));
        $new_year = new Year($this->kernel, $year->get('id'), false);
        $this->assertTrue($new_year->save(array('label' => '2000 - edited', 'locked' => 0, 'from_date' => '2000-1-1', 'to_date' => '2000-12-31', 'last_year_id' => 0)) > 0);
        $this->assertTrue($new_year->get('id') == $year->get('id'));
        $this->assertTrue($new_year->get('label') == '2000 - edited');
    }

    function testIsBalancedReturnsTrueWhenNoPostsHasBeenAdded()
    {
        $year = new Year($this->kernel);
        $id = $year->save(array('label' => '2000', 'locked' => 0, 'from_date' => '2000-1-1', 'to_date' => '2000-12-31', 'last_year_id' => 0));
        $this->assertTrue($year->isBalanced());
    }

}