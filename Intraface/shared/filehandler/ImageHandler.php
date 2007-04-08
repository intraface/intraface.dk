<?php

 /**
  * Image handler. Klarer håndtering af billeder.
	*
	* @author: Sune
	* @version: 1.0
	*
	*/

class ImageHandler extends Standard {
	
	var $file_handler;
	
	function imagehandler(&$file_handler) {
		if(!is_object($file_handler)) {
			trigger_error("InstanceHandler kræver et filehandler- eller filemanagerobject i InstanceHandler->instancehandler (1)", E_USER_ERROR);
		}
		
		if(strtolower(get_class($file_handler)) == 'filehandler' || strtolower(get_class($file_handler)) == 'filemanager') {
			// HJÆLP MIG, jeg kan ikke vende denne if-sætning rigtigt. 
			// Men her er det ok.
		}
		else {
			trigger_error("InstanceHandler kræver et filehandler- eller filemanagerobject i InstanceHandler->instancehandler (2)", E_USER_ERROR);
		}
		
		$this->file_handler = &$file_handler;
	
		if($this->file_handler->get('is_image') != 1) {
			trigger_error("Filtypen " . $file_type['mime_type'] . " er ikke et billede, og kan derfor ikke manipuleres i ImageHandler", E_USER_ERROR);
		}
		
		if(!is_dir($this->file_handler->tempdir_path)) {
			if(!mkdir($this->file_handler->tempdir_path)) {
				trigger_error("Kunne ikke oprette workdir i ImageHandler->imageHandler", E_USER_ERROR);
			}
		}
		
	}
	
	function resize($width, $height = NULL, $strict = 'relative') {
		
		
		$image = Image_Transform::factory(IMAGE_LIBRARY);
		$error = $image->load($this->file_handler->get('file_path'));
		
		$image->setOption('quality', 100);
		
		if($error !== true) {
			trigger_error("Kunne ikke åbne fil i ImageHandler->resize. ".$error->getMessage(), E_USER_ERROR);
		}
		
		if(!in_array($strict, array('relative', 'strict'))) trigger_error("Den tredje parameter i ImageHandle->resize er ikke 'strict' eller 'relative'.", E_USER_ERROR);
		
		// die($width.":".$height.":".$strict);
		
		if($strict == 'strict') {
			// skal lige resizes først!
			if ($image->img_x > $image->img_y) {
				$image->scaleByY($height);
				
				$offset_y = 0;
				$offset_x = ($image->new_x - $width)/2;
			}
			else {
				$image->scaleByX($width);
				$offset_y = ($image->new_y - $height)/2;
				$offset_x = 0;
			}
			
			// die($width.":".$height.":".$offset_x.":".$offset_y);
			
			if($image->crop($width, $height, $offset_x, $offset_y) !== true){
				trigger_error("Der opstod en fejl under formatering (crop) af billedet i ImageHandler->resize", E_USER_ERROR);
			}
		}
		else{
			
			
			if($image->fit($width, $height) !== true) {
				trigger_error("Der opstod en fejl under formatering (fit) af billedet i ImageHandler->resize", E_USER_ERROR);
			}
		}
		
		$file_type = $this->file_handler->get('file_type');
		
		$new_filename = $this->file_handler->tempdir_path.date('U').$this->file_handler->kernel->randomKey(10).'.'.$file_type['extension'];
		
		if($image->save($new_filename) !== true) {
			trigger_error("Kunne ikke gemme billedet i ImageHandler->resize", E_USER_ERROR);
		}
		
		return $new_filename;
	
	}
	
	
	function getRelativeSize($max_width, $max_height) { //100 //67
		
		$width = $this->file_handler->get('width'); //1000
		$height = $this->file_handler->get('height'); //502
		
		
		if($width > $max_width) {
			$height = ($max_width/$width)*$height;
			$width = $max_width;
		}
		
		
		if($height > $max_height) {
			$width = ($max_height/$height)*$width;
			$height = $max_height;
		}
		
		return array('width' => round($width), 'height' => round($height));
	}
	
}
?>
