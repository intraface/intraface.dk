<?php
/**
 *
 * @package <SystemMessage>
 * @author	<Sune>
 * @since	1.0
 * @version	1.0 
 *
 */
 

 
class SharedKeyword Extends Shared {

	function SharedKeyword() {
		$this->shared_name = 'keyword'; // Navn på på mappen med modullet
		$this->active = 1; // Er shared aktivt
		
		$this->addPreloadFile('Keyword.php');
	}
}

?>