<?php
require_once dirname(__FILE__) . '/../config.test.php';

require_once 'PHPUnit/Framework.php';

require_once 'Intraface/DBQuery.php';
require_once 'Intraface/Error.php';

class FakeDBQueryKernel {

    public $user;

    public function __construct()
    {
        $this->user = new FakeDBQueryUser;
    }

}

class FakeDBQueryUser {
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

    function createKernel()
    {
        return new FakeDBQueryKernel;
    }

    function createDBQuery()
    {
        $kernel = $this->createKernel();
        return new DBQuery($kernel, $this->table);
    }

    function insertPosts()
    {
        $data = array('one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten');
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
        $this->assertEquals($this->table, $dbquery->table);
    }

    function testRequiredConditions()
    {
        $condition = 'name = 1';
        $kernel = $this->createKernel();
        $dbquery = new DBQuery($kernel, $this->table, $condition);
        $this->assertEquals($condition, $dbquery->required_conditions);
    }

    function testGetCharacters()
    {
        $dbquery = $this->createDBQuery();
        $db = $dbquery->getRecordset('*', '', false);
        $this->assertEquals(10, $db->numRows());
        $dbquery->useCharacter();
        $dbquery->defineCharacter('t', 'name');
        $this->assertTrue($dbquery->use_character);
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

        $this->assertEquals($paging_name, $dbquery->paging_var_name);

        $paging = $dbquery->getPaging();
        $expected_offset = array(1=>0, 2=>2, 3=>4, 4=>6, 5=>8);
        $this->assertEquals($expected_offset, $paging['offset']);
        $this->assertEquals(0, $paging['previous']);
        $this->assertEquals(2, $paging['next']);
    }

}
?>