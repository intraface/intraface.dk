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

require_once 'Ilib/Error.php';
class Error Extends Ilib_Error {
    
    public function __construct() {
        parent::_construct();
    }
}

// This can be deleted when intraface 1.7 is running on the server
class _old_Error {

    private $message;
    public $viewer;

    /**
     * Constructor
     */
    public function __construct() {
        $this->message = array();
    }

    /**
     * sætter er en fejlbesked
     *
     * @param string $msg fejlbeskeden
     *
     * @return void
     */
    public function set($msg) {
        if(!empty($msg)) {
            $this->message[] = $msg;
        } else {
            $this->message[] = 'Udefinderet fejlbesked!';
        }
    }

    /**
     * merge another error array with this
     *
     * @param array $error_array array provided with errormessages
     *
     * @return void
     */
    public function merge($error_array) {
        if(is_array($error_array)) {
            $this->message = array_merge($this->message, $error_array);
        }
    }

    /**
     * Returnere om der er fejl
     *
     * @return boolean true hvis fejl, false hvis ikke
     */
    public function isError() {
        if($this->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Tæller antaller af fejl
     *
     * @return integer antallet af fejl
     */
    public function count() {
        return(count($this->message));
    }

    /**
     * Returnere fejlbeskeder som et array
     *
     * @return array Array med fejlbeskeder
     */
    public function getMessage() {
        return($this->message);
    }

    /**
     * View the messages
     *
     * @param object $translation Translation object
     *
     * @return void echoes out a string
     */
    public function view($translation = '') {
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
class _old_ErrorHtmlViewer {

    private $error;

    /**
     * Constructor
     *
     * Ved at lave en reference til $error burde den kunne samle
     * alle ændringer sammen.
     */
    public function __construct($error) {
        if (!is_object($error) OR strtolower(get_class($error)) != 'error') {
            die('ErrorHtmlViewer kræver Errorobjekt');
        }
        $this->error = $error;
    }

    /**
     * Views the error
     *
     * @param object $translation
     *
     * @return string
     */
    public function view($translation) {
        if (is_object($translation)) {
            $e = '<ul class="formerrors">';
            foreach ($this->error->getMessage() AS $error) {
                $e .= '<li>' . $translation->get($error) . '</li>';
            }
            $e .= '</ul>';
            return $e;
        } else {
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