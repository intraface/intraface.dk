<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'Intraface/DBQuery.php';
require_once 'Ilib/DBQuery.php';
require_once 'Intraface/Error.php';

class FakeDBQueryKernel {

    public $user;

    public function __construct($session_id = '')
    {
        $this->user = new FakeDBQueryUser;
        $this->intranet = new FakeDBQueryIntranet;
        if($session_id == '') {
            $this->session_id = 'dkm30dekcmek3dk30dkqdlfrjdi3i2jdnft';
        }
        else {
            $this->session_id = $session_id;
        }

    }

    public function getSessionId() {
        return $this->session_id;
    }

}

class FakeDBQueryUser {
    public function get()
    {
        return 1;
    }
}

class FakeDBQueryIntranet {
    public function get()
    {
        return 1;
    }
}

class DBQueryTest extends PHPUnit_Framework_TestCase
{
    private $db;
    private $table = 'dbquery_test';

    function setUp()
    {
        $this->db = MDB2::factory(DB_DSN);
        if (PEAR::isError($this->db)) {
            die($this->db->getUserInfo());
        }

        $result = $this->db->exec('TRUNCATE TABLE dbquery_result');

        $result = $this->db->exec('DROP TABLE ' . $this->table);
        /*
         TODO: DROP THE TABLE IF IT EXISTS

        $result = $this->db->exec('DROP TABLE ' . $this->table);

        if (PEAR::isError($result)) {
            die($result->getUserInfo());
        }
        */

        $result = $this->db->exec('CREATE TABLE ' . $this->table . '(
            id int(11) NOT NULL auto_increment, name varchar(255) NOT NULL, PRIMARY KEY  (id))'
        );

        if (PEAR::isError($result)) {
            die($result->getUserInfo());
        }

        $this->insertPosts();
    }

    function createDBQuery($session_id = '')
    {
        $kernel = new FakeDBQueryKernel($session_id);
        return new DBQuery($kernel, $this->table);
    }

    function insertPosts()
    {
        $data = array('one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen', 'twenty', 'twentyone');
        foreach ($data as $d) {
            $this->createPost($d);
        }
    }

    function createPost($post)
    {
        $result = $this->db->exec('INSERT INTO ' . $this->table . ' (name) VALUES ('.$this->db->quote($post, 'text').')');
        if (PEAR::isError($result)) {
            die($result->getUserInfo());
        }
    }

    function tearDown()
    {
        $result = $this->db->exec('DROP TABLE ' . $this->table);
    }

    ///////////////////////////////////////////////////////////////////////////

    function testConstructor()
    {
        $dbquery = $this->createDBQuery();
        $this->assertTrue(is_object($dbquery));
        $this->assertEquals($this->table, $dbquery->getTableName());
    }

    function testRequiredConditions()
    {
        $condition = 'name = 1';
        $kernel = new FakeDBQueryKernel;
        $dbquery = new DBQuery($kernel, $this->table, $condition);
        $this->assertEquals($condition, $dbquery->required_conditions);
    }

    function testGetCharacters()
    {
        $dbquery = $this->createDBQuery();
        $db = $dbquery->getRecordset('*', '', false);
        $this->assertEquals(21, $db->numRows());
        $dbquery->useCharacter();
        $dbquery->defineCharacter('t', 'name');
        $this->assertTrue($dbquery->getUseCharacter());
        $characters = $dbquery->getCharacters();
        $this->assertEquals(6, count($characters));
    }

    function testPaging()
    {
        $dbquery = $this->createDBQuery();
        $paging_name = 'paging';
        $rows_pr_page = 2;
        $dbquery->usePaging($paging_name, $rows_pr_page);
        $db = $dbquery->getRecordset('*', '', false);

        $this->assertEquals($paging_name, $dbquery->getPagingVarName());

        $paging = $dbquery->getPaging();
        $expected_offset = array(1=>0, 2=>2, 3=>4, 4=>6, 5=>8, 6=>10, 7=>12, 8=>14, 9=>16,10=>18,11=>20);
        $this->assertEquals($expected_offset, $paging['offset']);
        $this->assertEquals(0, $paging['previous']);
        $this->assertEquals(2, $paging['next']);
    }

    function testGetRecordset() {
        $dbquery = $this->createDBQuery();

        $dbquery->setCondition('id > 2');

        $db = $dbquery->getRecordset('id, name');
        $i = 0;
        while($db->nextRecord()) {
            $result[$i]['id'] = $db->f('id');
            $result[$i]['name'] = $db->f('name');
            $i++;
        }

        $this->assertEquals(19, count($result));
    }

    function testUseStoreOnTopLevel() {
        $dbquery = $this->createDBQuery();
        $dbquery->setCondition('id > 10');
        $dbquery->storeResult("use_stored", 'unittest', "toplevel");
        $db = $dbquery->getRecordset('id, name');


        $dbquery = $this->createDBQuery();
        $_GET['use_stored'] = 'true';
        $dbquery->storeResult("use_stored", 'unittest', "toplevel");
        $db = $dbquery->getRecordset('id, name');
        $i = 0;
        while($db->nextRecord()) {
            $result[$i]['id'] = $db->f('id');
            $result[$i]['name'] = $db->f('name');
            $i++;
        }
        $this->assertEquals(11, count($result));
    }

    function testUseStoreOnTopLevelWithAnotherOneInBetween() {
        // the first page
        $dbquery = $this->createDBQuery();
        $dbquery->setCondition('id > 10');
        $dbquery->storeResult("use_stored", 'unittest', "toplevel");
        $db = $dbquery->getRecordset('id, name');

        // another page also with toplevel - overrides the first one saved
        $dbquery = $this->createDBQuery();
        $dbquery->storeResult("use_stored", 'unittest-on-another-page', "toplevel");
        $db = $dbquery->getRecordset('id, name');

        // then back to the first page again - the result should not be saved
        $dbquery = $this->createDBQuery();
        $_GET['use_stored'] = 'true';
        $dbquery->storeResult("use_stored", 'unittest', "toplevel");
        $db = $dbquery->getRecordset('id, name');
        $i = 0;
        while($db->nextRecord()) {
            $result[$i]['id'] = $db->f('id');
            $result[$i]['name'] = $db->f('name');
            $i++;
        }
        $this->assertEquals(21, count($result));
    }

    function testUseStoreOnSublevelNotChangingToplevel() {
        // the first page
        $dbquery = $this->createDBQuery();
        $dbquery->setCondition('id > 10');
        $dbquery->storeResult("use_stored", 'unittest', "toplevel");
        $db = $dbquery->getRecordset('id, name');

        // another page with sublevel - does not override the first one saved
        $dbquery = $this->createDBQuery();
        $dbquery->storeResult("use_stored", 'unittest-on-another-page', "sublevel");
        $db = $dbquery->getRecordset('id, name');

        // then back to the first page again - the result should be saved
        $dbquery = $this->createDBQuery();
        $_GET['use_stored'] = 'true';
        $dbquery->storeResult("use_stored", 'unittest', "toplevel");
        $db = $dbquery->getRecordset('id, name');
        $i = 0;
        while($db->nextRecord()) {
            $result[$i]['id'] = $db->f('id');
            $result[$i]['name'] = $db->f('name');
            $i++;
        }
        $this->assertEquals(11, count($result));
    }

    function testUseStoreWithTwoDifferentUsers() {
        // the first page
        $dbquery = $this->createDBQuery();
        $dbquery->setCondition('id > 10');
        $dbquery->storeResult("use_stored", 'unittest', "toplevel");
        $db = $dbquery->getRecordset('id, name');

        // another user on the same page
        $dbquery = $this->createDBQuery('another-session-id-passed-to-kernel-and-then-to-dbquery');
        $dbquery->storeResult("use_stored", 'unittest', "toplevel");
        $db = $dbquery->getRecordset('id, name');

        // then back to the first page again - the result should be saved
        $dbquery = $this->createDBQuery();
        $_GET['use_stored'] = 'true';
        $dbquery->storeResult("use_stored", 'unittest', "toplevel");
        $db = $dbquery->getRecordset('id, name');
        $i = 0;
        while($db->nextRecord()) {
            $result[$i]['id'] = $db->f('id');
            $result[$i]['name'] = $db->f('name');
            $i++;
        }
        $this->assertEquals(11, count($result));
    }

}
?>