<?php
/**
 * Måske kunne klassen også indeholde oplysninger om at oplyse (fade) en side,
 * så vi får en standard for det?
 *
 * Skal startes op i kernel, når det er intranetlogin.
 */

class Core_Message {

	var $types = array('confirmation', 'message', 'warning', 'error');

	function Core_Message() {
	}

	function set($type, $message, $identifier) {
	}


}

?>