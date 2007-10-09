<?php

/**
 * Fejlhåndtering
 *
 * Bruges til at samle og returnere fejlbeskeder
 *
 * @author Sune Jensen
 * @author Lars Olesen
 * @version 1.1
 */

class Error {

	var $message;
	var $viewer;
	
	/**
	 * Init
	 */
	
	function Error() {
		$this->message = array();
	}
	
	/**
	 * sætter er en fejlbesked
	 * 
	 * @param (string)fejlbeskeden
	 */
	
	function set($msg) {
		if(!empty($msg)) {
			$this->message[] = $msg;
		}
		else {
			$this->message[] = 'Udefinderet fejlbesked!';
		}
	}
    
    /**
     * merge another error array with this 
     * 
     * @param (array)$error_array array provided with errormessages
     * @return void
     */
    function merge($error_array) {
        if(is_array($error_array)) {
            $this->message = array_merge($this->message, $error_array);
        }
        
    }
	
	/**
	 * Returnere om der er fejl
	 * 
	 * @return (boolean) true hvis fejl, false hvis ikke
	 */
	
	function isError() {
		if($this->count() > 0) {
			return(true);
		}
		else {
			return(false);
		}
	}
	
	/**
	 * Tæller antaller af fejl
	 *
	 * @return (integer) antallet af fejl
	 */
	
	function count() {
		return(count($this->message));
	}
	
	/**
	 * Returnere fejlbeskeder som et array
	 *
	 * @return (array) Array med fejlbeskeder
	 */
	
	function getMessage() {
		return($this->message);
	}
	
	function view($translation = '') {
		if ($this->count() > 0) {
			$this->viewer = new ErrorHtmlViewer($this);
			echo $this->viewer->view($translation);
		}
	}
	
}

/** 
 * Bruges til at udskrive fejlmeddelelser 
 *
 * @author Lars Olesen <lars@legestue.net>
 */

class ErrorHtmlViewer {

	var $error;

	/**
	 * 
	 *
	 * Ved at lave en reference til $error burde den kunne samle
	 * alle ændringer sammen. 
	 */
	
	function ErrorHtmlViewer(&$error) {
		if (!is_object($error) OR strtolower(get_class($error)) != 'error') {
			die('ErrorHtmlViewer kræver Errorobjekt');
		}
		$this->error = &$error;
	}
	
	function view($translation) {
		if (is_object($translation)) {
			$e = '<ul class="formerrors">';
			foreach ($this->error->getMessage() AS $error) { 
				$e .= '<li>' . $translation->get($error) . '</li>';
			}
			$e .= '</ul>';
			return $e;
		}
		else
		{
			$e = '<ul class="formerrors">';
			foreach ($this->error->getMessage() AS $error) { 
				$e .= '<li>' . $error . '</li>';
			}
			$e .= '</ul>';
			return $e;
		}
	}

}

?>