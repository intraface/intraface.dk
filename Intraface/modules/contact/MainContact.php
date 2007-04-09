<?php
/**
 * Kontakt
 *
 * @package Contact
 * @author	Lars Olesen
 * @since	1.0
 * @version	1.0
 */
 
class MainContact Extends Main {

	function MainContact() {
		$this->module_name = 'contact';
		$this->menu_label = 'Kontakter'; // Navnet der vil stå i menuen
		$this->show_menu = 1; // Skal modulet vises i menuen.
		$this->active = 1; // Er modulet aktivt.
		$this->menu_index = 120;
		$this->frontpage_index = 20;
		
		$this->addPreloadFile('Contact.php');
		$this->addPreloadFile('ContactMessage.php'); // kan måske slettes
		$this->addPreloadFile('ContactPerson.php');		
		
		$this->addRequiredShared('email');
		$this->addRequiredShared('keyword');		

		$this->addControlPanelFile('contact', '/modules/contact/setting.php');		

		$this->addFrontpageFile('include_frontpage.php');

		// Tilføj undermenu punkter.
		
		// $this->addSubMenuItem("Indstillinger", "setting.php");
		// $this->addSubMenuItem("Underside 2", "underside2.php");

		// Tilføj subaccess punkter
		// opretkunde: et kort navn der er sigende
		// Opret ny kunde: En beskrivelse af subaccess.
		//$this->addSubAccessItem("opretkunde", "Opret ny kunde"); 

		// settings
		// paymentconditions in days	
 		$this->includeSettingFile('settings.php');

		$this->addSetting('paymentcondition', array(0, 8, 14, 30));
		$this->addSetting('type',
			array(
				0 => 'private',
				1 => 'corporation'
			) 
		);
    
	 	$this->addSetting('contact_login_url', array(
			0 => 'kundelogin.dk',
			1 => 'medlemslogin.dk'
		));
	 
		$this->addSetting('preferred_invoice',
			array(
				1 => 'pdf',
				2 => 'email',
				3 => 'electronic'
			) 
		);
		
		$this->addSetting('reminder_status',
			array(
				0 => '_Invalid type_',
				1 => 'created',
				2 => 'seen',
				3 => 'cancelled'
			) 
		);
    
	}

}

?>