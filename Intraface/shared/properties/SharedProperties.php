<?php
/**
 *
 * @package <SystemMessage>
 * @author	<Sune>
 * @since	1.0
 * @version	1.0 
 *
 */
 

 
class SharedProperties Extends Shared {

	function SharedProperties() {
		$this->shared_name = 'properties'; // Navn på på mappen med modullet
		$this->active = 1; // Er shared aktivt
		
		$this->addPreloadFile('Properties.php');
	}
}

?>