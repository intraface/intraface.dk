<?php
/**
 * Newsletter
 *
 * Håndterer det enkelte nyhedsbrev, som skal udsendes til subscriber. 
 *
 * Nyhedsbrevet gemmes i databasen, og der er mulighed for at redigere det først
 * over flere omgange og sende det senere. 
 *
 * @package     Newsletter
 * @author      Lars Olesen <lars@legestue.net>
 * @version     1.0
 * @access      public
 * @copyright   Lars Olesen
 * @see         NewsletterList
 * @see         NewsletterSubscriber
 */
class Newsletter extends Standard {

	var $list; //object
	var $value = array();
	var $id;
	var $error;
	var $intranet_id;
	var $status = array(
		0 => 'created',
		1 => 'sent'
	);
	
	/**
	 * Kan kaldes med:
	 *  new Newsletter($kernel, id);
	 *  new Newsletter($newsletterlist_object);
	 */
	
	function Newsletter(&$list, $id = 0) {

		if(!is_object($list) || strtolower(get_class($list)) != 'newsletterlist') {
			trigger_error('Newsletter Kræver en liste - har '.get_class($list), E_USER_ERROR);
		}
		$this->list = &$list;
		$this->id = $id;
		$this->error = new Error;
		
		if($this->id > 0) {
			$this->load();
		}
		
		
		#
		# Hvorfor har du lavet det om?
		#
		#
		
	
		/*
		
		$argument = func_get_args();
		
		if (!is_array($argument)) {
			 trigger_error('Newsletter skal have nogle argumenter', E_USER_ERROR);
		}
		elseif (!empty($argument[1]) AND is_numeric($argument[1])) {	
			$this->id = (int)$argument[1];
			$kernel = & $argument[0];
			if ($this->id > 0) {
				$db = new DB_Sql;
				$db->query("SELECT list_id, intranet_id FROM newsletter_archieve WHERE id = " . $this->id);
				
				if ($db->nextRecord()) {	
					$this->list = new NewsletterList($kernel, $db->f('list_id'));
				}
				if (!$this->load()) {
					trigger_error('Newsletter kunne ikke loade', E_USER_ERROR);
				}
			}
		}
		elseif (!empty($argument[0]) AND get_class($argument[0]) == 'newsletterlist') {
			$this->list = $argument[0];
		}
		*/

		
		
		
	}
	
	function factory(&$kernel, $id) {
		
		$db = new DB_Sql;
		$db->query("SELECT list_id FROM newsletter_archieve WHERE intranet_id = ".$kernel->intranet->get('id')." AND active = 1 AND id = ".intval($id));
		if($db->nextRecord()) {
			$list = new NewsletterList($kernel, $db->f('list_id'));
			$letter = new Newsletter($list, $id);
			return $letter;
		}
		trigger_error('Ugyldigt id', E_USER_ERROR);
	}
	
	
	/**
	 *
	 */
	function load() {
		$db = new DB_Sql;
	 	$db->query("SELECT id, list_id, subject, text, deadline, sent_to_receivers, status FROM newsletter_archieve WHERE id = " . $this->id . " AND active = 1 LIMIT 1");

		$db2 = new DB_Sql;		
		if ($db->nextRecord()) {
			$this->value['id'] = $db->f('id');
			$this->value['list_id'] = $db->f('list_id');			
			$this->value['subject'] = $db->f('subject');
			$this->value['text'] = $db->f('text');
			//$this->value['sent'] = $db->f('sent');
			$this->value['deadline'] = $db->f('deadline');
			$this->value['sent_to_receivers'] = $db->f('sent_to_receivers');			
			$this->value['status_key'] = $db->f('status');
			$this->value['status'] = $this->status[$db->f('status')];

			/*
			Her skal vi lige have lavet noget status med hvor mange der modtager nyhedsbrevet
			$db2->query("SELECT id FROM email WHERE letter_id = " . $this->id. " AND intranet_id = " . $this->list->kernel->intranet->get('id'));
			$lettercount = $db->numRows();
			$db->query("SELECT id FROM newsletter_queue WHERE letter_id = " . $this->id . " AND status = 1  AND intranet_id = " . $this->list->kernel->intranet->get('id'));
			$lettersent = $db->numRows();
			if ($lettercount == 0) $status = 100; else $status = round($lettersent / $lettercount * 100);
		
			return( array('status' => $status, 'receivers' => $lettercount));
			$this->value['status'] = $status['status'];
			$this->value['receivers'] = $status['receivers'];			  			
			*/

		}
		return ($this->id = $db->f('id'));
	}
	
	/**
	 * Henter alle på en liste
	 */
	function getList() {
		$list = array();
		$db = new DB_Sql;
		$db->query("SELECT * FROM  newsletter_archieve WHERE active = 1 AND list_id = " . $this->list->get('id') . " ORDER BY deadline DESC");
		$i = 0;
		while ($db->nextRecord()) {
			
			$list[$i]['subject'] = $db->f('subject');
			$list[$i]['id'] = $db->f('id');
			//$list[$i]['sent'] = $db->f('sent');
			
			$newsletter = new Newsletter($this->list, $db->f('id'));
			
			$list[$i]['status'] = $newsletter->get('status');
			$list[$i]['sent_to_receivers'] = $newsletter->get('sent_to_receivers');
			$i++;													
		}
		return($list);
	}
	
	function delete() {
		if ($this->get('locked') == 1) {
			$this->error->set('Nyhedsbrevet er låst');
			return 0;
		}
		$db = new DB_Sql;
		$db->query("UPDATE newsletter_archieve SET active = 0 WHERE id = " . $this->get("id") . "  AND intranet_id = " . $this->list->kernel->intranet->get('id') . " AND locked = 0");
		
		return 1;
	}
	

	function save($var) {
  
		$var = safeToDb($var);
		$var = array_map('strip_tags', $var);
    
		$validator = new Validator($this->error);
		$validator->isString($var['text'], 'Ugyldige tegn brug i tekst');
		$validator->isString($var['subject'], 'Ugyldige tegn brugt i emne');    
    
		if ($this->error->isError()) {
			return 0;
		}

		if ($this->id == 0) {
			$sql_type = "INSERT INTO";
			$sql_end = ', date_created = NOW()';
		}
		else {
			$sql_type = "UPDATE";
			$sql_end = " WHERE id = " . $this->id;		
		}
		$db = new DB_Sql;
		$sql = $sql_type . " newsletter_archieve	
			SET subject = '".$var['subject']."', 
			text = '".$var['text']."',
			intranet_id = ".$this->list->kernel->intranet->get('id').",
			deadline = '".$var['deadline']."',
			list_id = ".$this->list->get('id');
		if (empty($var['deadline'])) {
			$sql .= ", deadline = NOW()";
		}
		$sql .= $sql_end;
		$db->query($sql);
		
		if ($this->id == 0) {
			return $db->insertedId();
		}
		
		return $this->id;
	}


	/**
	 * Benyttes af NewsletterSender til at vise at brevet er sendt
	 */
	
	function updateSent($receivers) {
		$db = new DB_Sql;
		$db->query("UPDATE newsletter_archieve SET status = 1, sent_to_receivers = '".(int)$receivers."' WHERE id = " . $this->id . " AND intranet_id = " . $this->list->kernel->intranet->get('id'));
	}

	function queue() {
		if ($this->get('sent') == 1) {
			$this->error->set('Nyhedsbrevet er allerede sendt');
			return 0;
		}


		$subscriber = new NewsletterSubscriber($this->list);
		$subscribers = $subscriber->getList();
		
		$validator = new Validator($this->error);
	
		$i = 0;
		foreach($subscribers AS $subscriber) {
			if(!$validator->isEmail($subscriber['contact_email'], "")) {
				// Hvis de ikke har en mail, kører vi videre med næste.
				CONTINUE;
			}
			
			// hvis kontakten ikke findes skal vedkommende også slettes fra nyhedsbrevet
			$contact = new Contact($this->list->kernel, $subscriber['contact_id']);
			if ($contact->get('id') == 0) {
				$subscription = new NewsletterSubscriber($this->list, $subscriber['id']);
				$subscription->delete();
				continue;
			}
			$email = new Email($this->list->kernel);
			$input = array(
				'subject' => $this->get('subject'),
				'body' => $this->get('text') . "\n\nLogin: " . $contact->get('login_url'),
				'from_email' => $this->list->get('reply_email'),
				'from_name' => $this->list->get('sender_name'),				
				'date_deadline' => $this->get('deadline'),
				'contact_id' => $subscriber['contact_id'],
				'type_id' => 8,
				'belong_to' => $this->get('id')
			);

			$email->save($input);
			//$email->error->view();
			#
			# E-mailen sættes i kø - hvis vi sender den med det samme tager det
			# alt for lang tid.
			#
			$email->send('queue');
			/*
			$sql = "INSERT INTO newsletter_queue SET deadline = '".$this->get('deadline')."', date_created = NOW(), letter_id = " . $this->get('id') . ", subscriber_id = " . $subscriber['id'] . ", intranet_id = " . $this->list->kernel->intranet->get('id');
			$db->query($sql);
			*/
			$i++;
		}
		$this->updateSent($i);
		return 1;
	}  

}

?>