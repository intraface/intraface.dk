<?php
require_once dirname(__FILE__) . './../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'Intraface/modules/accounting/Year.php';

class FakeYearKernel {}

class YearTest extends PHPUnit_Framework_TestCase {

    function setUp() {
        $this->kernel = new FakeYearKernel();
    }

    function testSaveMethod() {
        $this->markTestIncomplete('needs updating');
        $year = new Year($this->kernel);
        $this->assertFalse($year->get('id'));
        $this->assertTrue($year->save(array('label' => '2000', 'locked' => 0, 'from_date' => '2000-1-1', 'to_date' => '2000-12-31')));
        $this->assertEqual('2000', $year->get('label'));
        $new_year = new Year($this->kernel, $year->get('id'), false);
        $this->assertTrue($new_year->save(array('label' => '2000 - edited', 'locked' => 0, 'from_date' => '2000-1-1', 'to_date' => '2000-12-31')));
        $this->assertTrue($new_year->get('id') == $year->get('id'));
        $this->assertTrue($new_year->get('label') == '2000 - edited');

    }

}
?>