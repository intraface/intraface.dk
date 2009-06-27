<?php
require_once dirname(__FILE__) . './../config.test.php';

require_once 'PHPUnit/Framework.php';
require_once 'Intraface/modules/accounting/Post.php';

class FakePostSetting {
    function get() {}
}

class FakePostIntranet {
    function get() { return 1; }
    function getId() {
    	return $this->get();
    }
}
class FakePostUser {
    function get() { return 1; }
}

class FakePostKernel
{
    public $setting;
    public $intranet;
    public $user;
    function __construct()
    {
        $this->setting = new FakePostSetting;
        $this->intranet = new FakePostIntranet;
        $this->user = new FakePostUser;
    }
}

class FakePostVoucher
{
	public $year;
    function __construct()
    {
    	$this->year = new FakePostYear;
    }
    function get()
    {
    	return 1;
    }
}

class FakePostYear
{
    public $kernel;
    function __construct()
    {
        $this->kernel = new FakePostKernel;
    }
    function get() { return 1; }
    function vatAccountIsSet() { return true; }
}

class PostTest extends PHPUnit_Framework_TestCase
{
    private $voucher;

    function setUp()
    {
        $this->voucher = new FakePostVoucher;
    }

    function tearDown()
    {
        $db = MDB2::factory(DB_DSN);
    	$db->query('TRUNCATE accounting_post');
    }

    function testPostCreate()
    {
        // TODO needs to be updated
        $post = new Post($this->voucher);
        $this->assertTrue(is_object($post));
        $this->assertFalse($post->getId() > 0);
        $date = date('Y-m-d');
        $account_id = 1;
        $text = 'test';
        $debet = 1;
        $credit = 1;
        $skip_draft = false;
        $res = $post->save($date, $account_id, $text, $debet, $credit, $skip_draft);
        $this->assertTrue($res);

        /*
        $voucher->save(array('text' => 'Description', 'date' => '2002-10-10'));
        $new_voucher = new Voucher($this->year, $voucher->get('id'));
        $new_voucher->save(array('text' => 'Description - edited', 'date' => '2002-10-10'));
        $this->assertTrue($voucher->get('id') == $new_voucher->get('id'));
        $this->assertTrue($new_voucher->get('text') == 'Description - edited');
        */
    }

    function testFactory()
    {
        $post = new Post($this->voucher);
        $this->assertTrue(is_object($post->factory($this->voucher->year, 1)));
    }

    function testDelete()
    {
        $post = new Post($this->voucher);
        $this->assertTrue(is_object($post));
        $this->assertFalse($post->getId() > 0);
        $date = date('Y-m-d');
        $account_id = 1;
        $text = 'test';
        $debet = 1;
        $credit = 1;
        $skip_draft = true;
        $res = $post->save($date, $account_id, $text, $debet, $credit, $skip_draft);
        $this->assertTrue($res);
        $this->assertEquals(1, count($post->getList()));

    }
}