<?php
require_once dirname(__FILE__) . '/../config.test.php';
require_once 'PHPUnit/Framework.php';
require_once 'Intraface/shared/email/Email.php';

class FakeIntranet {
	function get() {
		return 1;
	}
}

class FakeUser {
	function get() {
		return 1;
	}
}
class FakeKernel {
	public $intranet;
	public $user;
}

class EmailTest extends PHPUnit_Framework_TestCase {

	private $kernel;

	function setUp() {
		$this->kernel = new FakeKernel;
		$this->kernel->intranet = new FakeIntranet;
		$this->kernel->user = new FakeUser;
	}

	function testConstruction() {
		$email = new Email($this->kernel);
		$this->assertTrue(is_object($email));
	}
	/*
	function testSave() {
		$belong_to_id = rand(1, 100000);
		$type_id = rand(1,5);
		$contact_id = rand(1, 100000);
		$data = array(
			'subject' => 'Some subject',
			'body' => 'some body',
			'from_name' => 'x',
			'from_email' => 'test@legestue.net',
			'belong_to' => $belong_to_id,
			'type_id' => $type_id,
			'contact_id' => $contact_id,
			'deadline' => date('Y-m-d H:i:s')
		);
		$email = new Email($this->kernel);
		$return = $email->save($data);

		$this->assertTrue((is_numeric($return) AND $return > 0));
		$this->assertEquals($email->get('subject'), $data['subject']);
		$this->assertEquals($email->get('body'), $data['body']);
		$this->assertEquals($email->get('belong_to_id'), $data['belong_to']);
		$this->assertEquals($email->get('type_id'), $data['type_id']);
		$this->assertEquals($email->get('contact_id'), $data['contact_id']);
		$this->assertEquals($email->get('from_name'), $data['from_name']);
		$this->assertEquals($email->get('from_email'), $data['from_email']);
		$this->assertEquals($email->get('user_id'), $this->kernel->user->get('id'));
	}

	function _testSaveWithEmptyFrom() {
		$belong_to_id = rand(1, 100000);
		$type_id = rand(1,5);
		$contact_id = rand(1, 100000);
		$data = array(
			'subject' => 'Some subject',
			'body' => 'some body',
			'from_name' => '',
			'from_email' => '',
			'belong_to' => $belong_to_id,
			'type_id' => $type_id,
			'contact_id' => $contact_id,
			'deadline' => date('Y-m-d H:i:s')
		);
		$email = new Email($this->kernel);
		$return = $email->save($data);

		$this->assertTrue((is_numeric($return) AND $return > 0));
		$this->assertEquals($email->get('subject'), $data['subject']);
		$this->assertEquals($email->get('body'), $data['body']);
		$this->assertEquals($email->get('belong_to_id'), $data['belong_to']);
		$this->assertEquals($email->get('type_id'), $data['type_id']);
		$this->assertEquals($email->get('contact_id'), $data['contact_id']);
		$this->assertEquals($email->get('from_name'), $data['from_name']);
		$this->assertEquals($email->get('from_email'), $data['from_email']);
		$this->assertEquals($email->get('user_id'), $this->kernel->user->get('id'));
	}
	*/

	function testALotOfSaveEmails() {
		$number = 200;
		for($i = 0; $i<$number; $i++) {
			$belong_to_id = rand(1, 100000);
			$type_id = rand(1,5);
			$contact_id = rand(1, 100000);
			$data = array(
				'subject' => 'Some subject',
				'body' => 'some body',
				'from_name' => 'x',
				'from_email' => 'test@legestue.net',
				'belong_to' => $belong_to_id,
				'type_id' => $type_id,
				'contact_id' => $contact_id,
				'deadline' => date('Y-m-d H:i:s')
			);
			$email = new Email($this->kernel);
			$return = $email->save($data);
		}
		$this->assertEquals($i, $number);
	}

}
?>