<?php

class EmailGateway {

	private $db;

	function __construct() {
		$this->db = MDB2::singleton(DB_DSN);
	}

	function addEmail($var) {
		$this->db->loadModule('Extended');
		$table_name = 'email';

		if(empty($var['from_name'])) {
			$var['from_name'] = '';
		}
		if(empty($var['from_email'])) {
			$var['from_email'] = '';
		}
		if(empty($var['date_deadline'])) {
			$var['date_deadline'] = date('Y-m-d H:i:s');
		}

		if ($this->id > 0) {
			$type = MDB2_AUTOQUERY_UPDATE;
			$where = 'id='.$this->db->quote($this->id, 'integer');
			$insert_fields = array();
		}
		else {
			$type = MDB2_AUTOQUERY_INSERT;
			$where = NULL;
			$insert_fields = array(
				'date_created' => date('Y-m-d H:i:s'),
				'belong_to_id' => intval($var['belong_to']),
				'type_id' => intval($var['type_id']),
				'contact_id' => intval($var['contact_id'])
			);
			$insert_types = array(
				'timestamp',
				'integer',
				'integer',
				'integer'
			);
		}

		$fields = array(
			'date_deadline' => $var['date_deadline'],
			'date_updated' => date('Y-m-d H:i:s'),
			'intranet_id' => $this->kernel->intranet->get('id'),
			'status' => 1,
			'body' => $var['body'],
			'subject' => $var['subject'],
			'from_name' => $var['from_name'],
			'from_email' => $var['from_email']
		);

		$types = array(
			'timestamp',
			'timestamp',
			'integer',
			'integer',
			'text',
			'text',
			'text',
			'text'

		);

		$fields = array_merge($insert_fields, $fields);
		$types = array_merge($insert_types, $types);

		$sth = $this->db->autoPrepare(
			$table_name,
			$table_fields,
			MDB2_AUTOQUERY_INSERT,
			null,
			$types
		);

		if (PEAR::isError($sth)) {
			die($sth->getMessage());
		}
	}

}

?>