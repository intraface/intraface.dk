<?php
/**
 *
 * @package <SystemMessage>
 * @author	<Sune>
 * @since	1.0
 * @version	1.0 
 *
 */
 

 
class SharedEmail Extends Shared {

	function SharedEmail() {
		$this->shared_name = 'email'; // Navn på på mappen med modullet
		$this->active = 1; // Er shared aktivt
		
		$this->addPreloadFile('Email.php');
	}
}

?>