<?php
require_once dirname(__FILE__) . './../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'Intraface/modules/accounting/Year.php';
require_once 'Intraface/functions/functions.php';
require_once 'Intraface/tools/Date.php';
require_once 'Intraface/Validator.php';
require_once 'DB/Sql.php';

class FakeYearSetting {
    function get() {}
}

class FakeYearIntranet {
    function get() {
        return 1;
    }
}

class FakeYearUser {
    function get() {
        return 1;
    }
}

class FakeYearKernel {
    public $setting;
    public $intranet;
    public $user;

    function __construct() {
        $this->setting = new FakeYearSetting;
        $this->intranet = new FakeYearIntranet;
        $this->user = new FakeYearUser;
    }
}

class YearTest extends PHPUnit_Framework_TestCase {

    function setUp() {
        $this->kernel = new FakeYearKernel();
    }

    function testSaveMethod() {
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

}
?>