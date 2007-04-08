<?php
/**
 * Kontakt
 *
 * @package Contact
 * @author	Lars Olesen
 * @since	1.0
 * @version	1.0
 */
 
class MainComment Extends Main {

	function MainComment() {
		MainComment::__construct();
	}

	function __construct() {
		$this->module_name = 'comment';
		$this->menu_label = 'comment'; // Navnet der vil stå i menuen
		$this->show_menu = 1; // Skal modulet vises i menuen.
		$this->active = 1; // Er modulet aktivt.
		$this->menu_index = 280;
		$this->frontpage_index = 200;
		
		$this->addRequiredShared('comment');

	}
	
}

?>