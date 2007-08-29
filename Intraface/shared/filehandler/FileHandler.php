<?php
/**
 * FileHandler
 *
 * Har grundlæggende kontrol over filer der uploades til systemet.
 * FileHandler i include/3party omdøbes til fileModifier
 * Filehandler benytter FileUpload og FileModifier.
 *
 * FileManager er modullet hvor man også kan se browse og ændre filerne. 
 * Dette vil benytte FileHandler.
 * 
 * @author	Sune Jensen
 * @since 1.2
 */
 
require_once('HTTP/Upload.php');
require_once('Image/Transform.php');
 
class FileHandler extends Standard {

	var $id; // file id
	var $kernel;
	var $error;
	var $upload_path;
	var $tempdir_path;
	var $values;
	var $file_types;
	var $accessibility_types;
	
	var $status = array(
		0 => 'visible',
		1 => 'temporary',
		2 => 'hidden'
		
	);
	
	var $upload;

	
	var $dbquery; // der er muligt, at der kun skal være en getList i filemanager,
	// men så skal vi have cms til at have filemanager som dependent. Foreløbig
	// har jeg lavet keywordsøgning i denne LO
	
	/**
	 * Init
	 * 
	 * FileUpload og FileHandler initieres med det samme, så man kan bruge
	 * deres funktioner, fx setMaxFileSize uden for klassen.
	 */
	
	function FileHandler(& $kernel, $file_id = 0) {
		if (!is_object($kernel) OR strtolower(get_class($kernel)) != 'kernel') {
			trigger_error('FileHandler kræver kernel', E_USER_ERROR);
		}
		$this->kernel = & $kernel;
		$this->id = (int)$file_id;
		$this->error = new Error;
		
		$filehandler_shared = $this->kernel->useShared('filehandler');
		$this->file_types = $filehandler_shared->getSetting('file_type');
		$this->accessibility_types = $filehandler_shared->getSetting('accessibility');
		$this->upload_path = PATH_UPLOAD.$this->kernel->intranet->get('id').'/';
		$this->tempdir_path = $this->upload_path.PATH_UPLOAD_TEMPORARY;
		
		
		
		if ($this->id > 0) {
			
			$this->load();
		}
		
	}
	
	function factory(&$kernel, $access_key) {
		
		$access_key = safeToDb($access_key);
		
		$db = new DB_Sql;
		$db->query("SELECT id FROM file_handler WHERE intranet_id = ".$kernel->intranet->get('id')." AND active = 1 AND access_key = '".$access_key."'");
		if(!$db->nextRecord()) {
			return false;
		}
		return new FileHandler($kernel, $db->f('id'));
	}
	
	
	function load() {
		
		$db = new DB_Sql;
		$db->query("SELECT id, date_created, width, height, date_changed, description, file_name, server_file_name, file_size, access_key, accessibility_key, file_type_key, DATE_FORMAT(date_created, '%d-%m-%Y') AS dk_date_created, DATE_FORMAT(date_changed, '%d-%m-%Y') AS dk_date_changed FROM file_handler WHERE id = ".$this->id." AND intranet_id = ".$this->kernel->intranet->get('id')." AND active = 1");		
		if(!$db->nextRecord()) {
			
			$this->id = 0;
			$this->value['id'] = 0;
			return 0;
		}
		
		$this->value['id'] = $db->f('id');
		$this->value['date_created'] = $db->f('date_created');
		$this->value['dk_date_created'] = $db->f('dk_date_created');
		$this->value['date_changed'] = $db->f('date_changed');
		$this->value['dk_date_changed'] = $db->f('dk_date_changed');
		$this->value['description'] = $db->f('description');
		if (empty($this->value['description'])) {
			$this->value['description'] = $db->f('file_name');
		}
		$this->value['name'] = $db->f('file_name'); // bruges af keywords
		$this->value['file_name'] = $db->f('file_name');
		$this->value['server_file_name'] = $db->f('server_file_name');
		$this->value['original_server_file_name'] = $this->value['server_file_name'];
		$this->value['file_size'] = $db->f('file_size');
		$this->value['access_key'] = $db->f('access_key');
		
		$this->value['accessibility'] = $this->accessibility_types[$db->f('accessibility_key')];
		
		if($this->value['file_size'] >= 1000000) {
			$this->value['dk_file_size'] = number_format(($this->value['file_size']/1000000), 2, ",",".")." Mb";
		}
		else if($this->value['file_size'] >= 1000) {
			$this->value['dk_file_size'] = number_format(($this->value['file_size']/1000), 2, ",",".")." Kb";
		}
		else {
			$this->value['dk_file_size'] = number_format($this->value['file_size'], 2, ",",".")." byte";
		}
		
		$this->value['file_type_key'] = (int)$db->f('file_type_key');
		$this->value['file_type'] = $this->_getMimeType((int)$db->f('file_type_key'));
		$this->value['file_path'] = $this->upload_path . $db->f('server_file_name');
		
		// denne skal kaldes efter getMimeType ellers er $this->file_types ikke instantieret
		$this->value['is_image'] = $this->file_types[$this->get('file_type_key')]['image'];
		
		if (file_exists($this->get('file_path'))) {
			$this->value['last_modified'] = filemtime($this->get('file_path'));
		}
		else {
			$this->value['last_modified'] = 'Filen findes ikke';
		}
		
		$this->value['file_uri'] = FILE_VIEWER.'?/'.$this->kernel->intranet->get('public_key').'/'.$this->get('access_key').'/'.urlencode($this->get('file_name'));
		// nedenstående bruges til pdf-er
		$this->value['file_uri_pdf'] = PATH_UPLOAD.$this->kernel->intranet->get('id').'/'.$this->value['server_file_name'];		

		if($this->value['is_image'] == 1) {
			$this->value['icon_uri'] = FILE_VIEWER.'?/'.$this->kernel->intranet->get('public_key').'/'.$db->f('access_key').'/square/'.urlencode($db->f('file_name'));
			$this->value['icon_width'] = 75;
			$this->value['icon_height'] = 75;
		}
		else {
			$this->value['icon_uri'] = PATH_WWW.'images/mimetypes/'.$this->value['file_type']['icon'];
			$this->value['icon_width'] = 75;
			$this->value['icon_height'] = 75;
		}
		
		if($this->value['is_image'] == 1) {
			if($db->f('width') == NULL) {
				$imagesize = getimagesize($this->get('file_path'));
				$this->value['width'] = $imagesize[0]; // imagesx($this->get('file_uri'));
				$db2 = new DB_sql;
				$db2->query("UPDATE file_handler SET width = ".(int)$this->value['width']." WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);
			}
			else {
				$this->value['width'] = $db->f('width');
			}
			
			if($db->f('height') == NULL) {
				$imagesize = getimagesize($this->get('file_path'));
				$this->value['height'] = $imagesize[1]; //imagesy($this->get('file_uri'));
				$db2 = new DB_sql;
				$db2->query("UPDATE file_handler SET height = ".(int)$this->value['height']." WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);
			}
			else {
				$this->value['height'] = $db->f('height');
			}
		}
		else {
			$this->value['width'] = '';
			$this->value['height'] = '';
		}
		
		return($this->id);
	}
	
	function createDBQuery() {
		$this->dbquery = new DBQuery($this->kernel, "file_handler", "file_handler.temporary = 0 AND file_handler.active = 1 AND file_handler.intranet_id = ".$this->kernel->intranet->get("id"));
	}	
	
	function createUpload() {
		
		if(!class_exists('UploadHandler')) {
			$filehandler_shared = $this->kernel->useShared('filehandler');
			$filehandler_shared->includeFile('UploadHandler.php');
		}
		$this->upload = new UploadHandler($this);
	}
	
	function createInstance($type = "", $param = array()) {
		if(!class_exists('InstanceHandler')) {
			$filehandler_shared = $this->kernel->useShared('filehandler');
			$filehandler_shared->includeFile('InstanceHandler.php');
		}
		
		if($type == "") {
			$this->instance = new InstanceHandler($this);
		}
		else {
			$this->instance = InstanceHandler::factory($this, $type, $param);
		}
	}
	
	function createImage() {
		if(!class_exists('ImageHandler')) {
			$filehandler_shared = $this->kernel->useShared('filehandler');
			$filehandler_shared->includeFile('ImageHandler.php');
		}
		
		$this->image = new ImageHandler($this);
	}
	
	/**
	 * Delete
	 *
	 * Sletter fil: Sætter active = 0 og sætter _deleted_ foran filen.
	 *
	 * Her bør sikkert være et tjek på om filen bruges nogen steder i systemet.
	 * Hvis den bruges skal man måske have at vide hvor?
	 *
	 * @access	public
	 */
	
	function delete() {
		if($this->id == 0) {
			return false;
		}
		
		$db = new DB_Sql;
		
		if($this->get('server_file_name') != '' && file_exists($this->get('file_path'))) {
		
			if(!rename($this->get('file_path'), $this->upload_path.'_deleted_'.$this->get('server_file_name'))) {
				trigger_error("Kunne ikke omdøbe filen i FileHandler->delete()", E_USER_ERROR);
			}
		}
		
		$db->query("UPDATE file_handler SET active = 0 WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);
		return true;
	}
	
	function undelete() {
		if($this->id == 0) {
			return false;
		}
		
		$db = new DB_Sql;
		$deleted_file_name = $this->upload_path . '_deleted_' . $this->get('server_file_name');
		if(file_exists($deleted_file_name)) {
		
			if(!rename($deleted_file_name, $this->upload_path.$this->get('server_file_name'))) {
				trigger_error("Kunne ikke omdøbe filen i FileHandler->delete()", E_USER_ERROR);
			}
		}
		
		$db->query("UPDATE file_handler SET active = 1 WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);
		return true;
	}
	
	
	/**
	 * Benyttes til at sætte en uploadet fil ind i systemet
	 *
	 *@param file: stien til filen
	 *@param file_name: det originale filnavn, hvis ikke sat, tages der efter det nuværende navn
	 *@param temporary: hvis sat til 'temporary' gemmes filen med temporary sat.
	 */
	function save($file, $file_name = '', $status = 'visible', $mime_type = NULL) {
		
		
		if(!is_file($file)) {
			$this->error->set("error in input - not valid file");
			return false;
		}
		
		if(!in_array($status, $this->status)) {
			trigger_error("Trejde parameter '".$status."' er ikke gyldig i Filehandler->save", E_USER_ERROR);
		}
		
		$db = new DB_Sql;
		
		if($file_name == '') {
			$file_name = substr(strrchr($file, '/'), 1);
		}
		else {
			$file_name = safeToDb($file_name);
		}
		
		// Vi sikre os at ingen andre har den nøgle
		$i = 0;
		do {
			$access_key = $this->kernel->randomKey(50);
			
			if($i > 50 || $access_key == '') {
				trigger_error("Fejl under generering af access_key i FileHandler->save", E_USER_ERROR);
			}
			$i++;
			$db->query("SELECT id FROM file_handler WHERE access_key = '".$access_key."'");
		} while($db->nextRecord()); 
		
		
		
		$file_size = filesize($file);
		if($mime_type === NULL) {
			// $mime_type = mime_content_type($file);
            require_once 'MIME/Type.php';
            $mime_type = MIME_Type::autoDetect($file);
            if(PEAR::isError($mime_type)) {
                trigger_error("Error in Filehandler->save() ".$mime_type->getMessage(), E_USER_ERROR);
                exit;
            }
        }
		
		$mime_type = $this->_getMimeType($mime_type, 'mime_type');
		if($mime_type === false) {
			$this->error->set('error in filetype');
			return false;
		}
		
		
		if($mime_type['image']) {
			$imagesize = getimagesize($file);
			$width = $imagesize[0]; // imagesx($file);
			$height = $imagesize[1]; // imagesy($file);
		}
		else {
			$width = "NULL";
			$height = "NULL";
		}
		
		$accessibility_key = array_search('intranet', $this->accessibility_types);
		
		/*
		if($temporary == 'temporary') {
			$temporary = 1;
		}
		else {
			$temporary = 0;
		}
		*/
		
		$sql = "date_changed = NOW(),
			access_key = '".$access_key."',
			file_name = '".$file_name."',
			file_size = '".(int)$file_size."',
			file_type_key = ".$mime_type['key'].",
			accessibility_key = ".$accessibility_key.",
			width = ".$width.",
			height = ".$height.",
			temporary = ".array_search($status, $this->status)."";
		
		
		if($this->id != 0) {
			$db->query("UPDATE file_handler SET ".$sql." WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);
			$id = $this->id;
            
            // deleting the old file
			if(!rename($this->get('file_path'), $this->upload_path.'_deleted_'.$this->get('server_file_name'))) {
                trigger_error("Was not able to rename file ".$this->get('file_path')." in Filehandler->save()", E_USER_NOTICE);
            }
			$this->createInstance();
            $this->instance->deleteAll();
            
		}
		else {
			$db->query("INSERT INTO file_handler SET ".$sql.", date_created = NOW(), intranet_id = ".$this->kernel->intranet->get('id').", user_id = ".$this->kernel->user->get('id'));
			$id = $db->insertedId();
		}
		
		if(!is_dir($this->upload_path)) {
			if(!mkdir($this->upload_path)) {
				trigger_error("Kunne ikke oprette mappe i FileHandler->save", E_USER_ERROR);
                Exit;
			}
		}
		
		$server_file_name = $id.'.'.$mime_type['extension'];
		
		if(!is_file($file)) {
			trigger_error("Filen vi vil flytte er ikke en gyldig fil i filehandler->save", E_USER_ERROR);
		}
		
		
		if(!rename($file, $this->upload_path.$server_file_name)) {
			$this->delete();
			trigger_error("Det var ikke muligt at flytte fil i Filehandler->save", E_USER_ERROR);
		}
		
		$db->query("UPDATE file_handler SET server_file_name = \"".$server_file_name."\" WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$id);
		$this->id = $id;
		// $this->load();
		return $this->id;
	}
	
	
	/**
	 * Benyttes til at opdaterer oplysninger om fil
	 *
	 *@param input: array med input
	 */
	function update($input) {
		
		$db = new DB_sql;
		
		if(!is_array($input)) {
			trigger_error("Input skal være et array i FileHandler->updateInstance", E_USER_ERROR);
		}
		
		$input = safeToDb($input);
		$validator = new Validator($this->error);
		
		$sql = array();
		
		$sql[] = 'date_changed = NOW()';
		
		/*
		
		access key opdateres nu ikke længere hver gang!
		
		// Vi sikre os at ingen andre har den nøgle
		$i = 0;
		do {
			$access_key = $this->kernel->randomKey(50);
			
			if($i > 50 || $access_key == '') {
				trigger_error("Fejl under generering af access_key i FileHandler->update", E_USER_ERROR);
			}
			$i++;
			$db->query("SELECT id FROM file_handler WHERE access_key = \"".$access_key."\"");
		} while($db->nextRecord()); 
		
		// Access key ændre ved hver opdatering. Det behøver den måske ikke, men giver sådan set større sikkerhed.
		$sql[] = 'access_key = "'.$access_key.'"';
		
		*/
		

		// følgende må ikke slettes - bruges i electronisk faktura
		if(isset($input['file_name'])) {
			$sql[] = 'file_name = "'.$input['file_name'].'"';
		}
		
		if(isset($input['server_file_name'])) {
			$sql[] = 'server_file_name = "'.$input['server_file_name'].'"';
		}
		/*
		// udgår
		if(isset($input['file_size'])) {
			$sql[] = 'file_size = '.(int)$input['file_size'];
		}

		// Udgår
		if(isset($input['tmp'])) {
			$sql[] = 'tmp = '.(int)$input['tmp'];
		}
		
		// Udgår
		if(isset($input['file_type'])) {
			$mime_type = $this->_getMimeType($input['file_type'], 'mime_type');
			if($mime_type === false) {
				$this->error->set($input['file_type'].' er en ugyldig filtype');
				return false;
			}  
			$sql[] = 'file_type_key = '.$mime_type['key'];
		}
		*/
		if(isset($input['description'])) {
			$validator->isString($input['description'], 'Fejl i udfyldelsen af beskrivelse', '<strong><em>', 'allow_empty');
			$sql[] = 'description = "'.$input['description'].'"';
		}
		
		// Vi sikre os at den altid bliver sat
		if($this->id == 0 && !isset($input['accessibility'])) {
			$input['accessibility'] = 'intranet';
		}
		
		if(isset($input['accessibility'])) {
			$accessibility_key = array_search($input['accessibility'], $this->accessibility_types);
			if($accessibility_key === false) {
				trigger_error("Ugyldig accessibility ".$input['accessibility']." i FileHandler->update", E_USER_ERROR);
			}
			
			$sql[] = 'accessibility_key = '.$accessibility_key;
		}
		
		if($this->error->isError()) {
			return false;
		}
		
		
		if($this->id != 0) {
			$db->query("UPDATE file_handler SET ".implode(', ', $sql)." WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);
		}
		else {
			$db->query("INSERT INTO file_handler SET ".implode(', ', $sql).", user_id = ".$this->kernel->user->get('id').", intranet_id = ".$this->kernel->intranet->get('id').", date_created = NOW()");
			$this->id = $db->insertedId();
		}
		
		return $this->id;
	}
	
	function _getMimeType($key, $from = 'key') {
		
		/* hack */
		require(PATH_INCLUDE_CONFIG . 'setting_file_type.php');
		$this->file_types = $_file_type;		
		/* hack slut */
		
		if($from == 'key') {
			if(!is_integer($key)) {
				trigger_error("Når der skal findes mimetype fra key (default), skal første parameter til FileHandler->_getMimeType være en integer", E_USER_ERROR);
			}
			return $this->file_types[$key];
		}
		
		if(in_array($from, array('mime_type', 'extension'))) {
			foreach($this->file_types AS $file_key => $file_type) {
				if($file_type[$from] == $key) {
					// Vi putter lige key med i arrayet
					$file_type['key'] = $file_key;
					return $file_type;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Metoden spytter et image-tag ud og det er xhtml.
	 * 
	 * @author Lars Olesen <lars@legestue.net>
	 *
	 * @param $alt Alternativ tekst, hvis billedet ikke vises
	 * @return img-tag eller en tom streng, hvis der ikke er noget billede at vise
	 **/
	/*
	function getImageHtml($alt = '', $attr = '') {
		if ($this->id == 0) {
			return '';
		}
		return '<img src="'.htmlspecialchars($this->get('file_uri')).'" alt="'.$alt.'" '.$attr.' />';
	}	
	*/
	
	function moveFromTemporary() {
		$db = new DB_Sql;
		$db->query("UPDATE file_handler SET temporary = 0 WHERE user_id = ".$this->kernel->user->get('id')." AND id = " . $this->id);
		return 1;
	}
	
	/**
	 * Foreløbig bruges denne funktion kun af CMS_Gallery, men hvis den ændres,
	 * lav venligst også tilsvarende ændringer i CMS_Gallery
	 */
	/*
	function getList() {
		// husk at sætte permission her også. kan ikke lige huske felterne
		$db = $this->dbquery->getRecordset("file_handler.id", "", false);
		$i = 0;
		while ($db->nextRecord()) {
			$filehandler[$i] = new FileHandler($this->kernel, $db->f('id'));
			$i++;
		}
		return $filehandler;
	}
	*/
	
}

?>