<?php
/**
 * Validator
 *
 * @author Lars Olesen <lars@legestue.net>
 * @author Sune Jensen <sj@sunet.dk>
 */

require_once 'Validate.php';

/**
 * Basically a collection of validator methods which logs to a logger
 *
 * @author Lars Olesen <lars@legestue.net>
 * @author Sune Jensen <sj@sunet.dk>
 */
class Validator {

    /**
     * @var object
     */
    private $error;

    /**
     * Constructor
     *
     * @param object $error Error object
     *
     * @return void
     */
    public function __construct($error)
    {
        if (!is_object($error) OR strtolower(get_class($error)) != 'error') {
            trigger_error("Validator kræver error objektet", E_USER_ERROR);
        }
        $this->error = $error;
    }

    /**
     * Validates an email
     *
     * @param string $email emailen which should be validated
     * @param string $msg error message
     * @param string $param streng indholdende en eller flere af:
     *      "allow_empty": allows the string to be empty
     *
     * @return boolean true or false
     */
    function isEmail($email, $msg = '', $allow_empty = '') {

        if ($allow_empty == 'allow_empty' AND empty($email)) {
            return true;
        }

        if (!Validate::email($email, CONNECTION_INTERNET)) {
            $this->error->set($msg);
            return false;
        }
        return true;
    }

    /**
     * Validates a date
     *
     * @param string $date  date which should be validated
     * @param string $msg   error message
     * @param string $param streng indholdende en eller flere af:
     *      "allow_empty": allows the string to be empty
     *
     * @return boolean true or false
     */
    function isDate($date, $msg = '', $params = '')
    {

        $params = explode(",", $params);

        if (array_search("allow_empty", $params) !== false && empty($date)) {
            return true;
        }

        // Gyldig datoformater
        // d: 01-31, 1-31
        // m: 01-12, 1-12
        // y: 0000-9999, 01-99, 1-99

        /**
         * HUSK AT RETTE I BÅDE VALIDATOR OG DATE
         */

        $d = "([0-3]?[0-9])";
        $m = "([0-1]?[0-9])";
        $y = "([0-9][0-9][0-9][0-9]|[0-9]?[0-9])";
        $s = "(-|\.|/| )";

        if(ereg("^".$d.$s.$m.$s.$y."$", $date, $parts)) {
            // true
        } elseif(ereg("^".$d.$s.$m."$", $date, $parts) && array_search("allow_no_year", $params) !== false) {
            $parts[5] = date("Y");
            // true
        } else {
            $this->error->set($msg);
            return false;
        }

        if(checkdate($parts[3], $parts[1], $parts[5])) {
            return true;
        } else {
            $this->error->set($msg);
            return false;
        }
    }

    /**
     * Validates a time
     *
     * @param string $time  time which should be validated
     * @param string $msg   error message
     * @param string $param streng indholdende en eller flere af:
     *      "allow_empty": allows the string to be empty
     *
     * @return boolean true or false
     */
    function isTime($time, $msg = '', $params = '')
    {
        $params = explode(",", $params);

        if (array_search("allow_empty", $params) !== false && empty($time)) {
            return true;
        }

        // Gyldig datoformater
        // t: 00-23, 0-23
        // m: 00-59
        // s: 00-59


        $t = "([0-2]?[0-9])";
        $m = "([0-5][0-9])";
        $s = "([0-5][0-9])";
        $i = "(\:)";

        if(ereg("^".$t.$i.$m.$i.$s."$", $time, $parts)) {
            // true

        } elseif(ereg("^".$t.$i.$m."$", $time, $parts) && array_search("must_have_second", $params) === false) {

            $parts[5] = '00';
            // true
        } else {
            $this->error->set($msg);
            return(false);
        }

        if(intval($parts[1] > 23)) {
            $this->error->set($msg);
            return false;
        } else {
            return true;
        }
    }

    /**
     * Validates an url
     *
     * @param string $url   url which should be validated
     * @param string $msg   error message
     * @param string $param streng indholdende en eller flere af:
     *      "allow_empty": allows the string to be empty
     *
     * @return boolean
     */
    function isUrl($url, $msg = '', $allow_empty = '')
    {
        if ($allow_empty == 'allow_empty' AND empty($url)) {
            return true;
        }
        return Validate::uri($url);
    }

    /**
     * Validering af streng
     *
     * @param string $string       strengen der skal valideres
     * @param string $msg          fejlbeskeden
     * @param string $allowed_tags html tags der er tilladte at benytte.
     * @param string $param        streng indholdende en eller flere af:
     *      "allow_empty": tillader større
     *
     * @return boolean
     */
    function isString($string, $msg = '', $allowed_tags = '', $allow_empty = '')
    {
        if ($allow_empty == 'allow_empty' AND empty($string)) {
            return true;
        } elseif (empty($string)) {
            $this->error->set($msg);
            return false;
        }

        $test_string = strip_tags($string, $allowed_tags);

        if ($test_string != $string) {
            $this->error->set($msg);
            return false;
        }
        return true;
    }

    /**
     * Validates a password
     *
     * @param string  $password   which should be validated
     * @param integer $min_length how short
     * @param integer $max_length how long
     * @param string  $msg        error msg
     * @param string  $param      streng indholdende en eller flere af:
     *      "allow_empty": allows the string to be empty
     *
     * @return boolean true or false
     */
    function isPassword($password, $min_length, $max_length, $msg = "", $param = "") {

        $params = explode(",", $param);
        if(array_search('allow_empty', $params) !== false && empty($password)) {
            return true;
        }

        if(ereg("^[a-zA-Z0-9]+$", $password)) {
            if(strlen($password) >= intval($min_length) && strlen($password) <= intval($max_length)) {
                return true;
            }
        }
        $this->error->set($msg);
        return false;
    }

    /**
     * Kontroller om den er numerisk
     *
     * @param string $string strengen der skal valideres
     * @param string $msg    fejlbeskeden
     * @param string $param  streng indholdende en eller flere af:
     *      "allow_empty": tillader større
     *      "greater_than_zero": kun større end nul
     *      "zero_or_greater": 0 eller større
     *	    "integer": tillader kun heltal
     *
     * @return boolean
     */
    function isNumeric($string, $msg = '', $param = '')
    {
        $params = explode(",", $param);

        $string = str_replace(".", "", $string);
        $string = str_replace(",", ".", $string);

        if (array_search('allow_empty', $params) !== false && empty($string)) {
            return true;
        } elseif(is_numeric($string)) {

            if(array_search("integer", $params) !== false) {
                if(intval($string) != $string) {
                    $this->error->set($msg);
                    return false;
                }
            }

            if(array_search("zero_or_greater", $params) !== false) {
                if($string >= 0) {
                    return true;
                } else {
                    $this->error->set($msg);
                    return false;
                }
            } elseif(array_search("greater_than_zero", $params) !== false) {
                if($string > 0) {
                    return true;
                } else {
                    $this->error->set($msg);
                    return false;
                }
            } else {
                return true;
            }
        } else {
            $this->error->set($msg);
            return false;
        }
    }

    /**
     * Kontroller om den er double
     *
     * @param string $number strengen der skal valideres
     * @param string $msg    fejlbeskeden
     * @param string $param  streng indholdende en eller flere af:
     *      "allow_empty": tillader større
     *      "greater_than_zero": kun større end nul
     *      "zero_or_greater": 0 eller større
     *          "integer": tillader kun heltal
     *
     * @return boolean
     */
    function isDouble($number, $msg = "", $param = "") {
        $params = explode(",", $param);

        if (array_search('allow_empty', $params) !== false && empty($number)) {
            return true;
        } elseif(ereg("^-?[0-9]+(\.[0-9]{3})*(,[0-9]{1,2})?$", $number)) {

            // $^
            $number = str_replace(".", "", $number);
            $number = str_replace(",", ".", $number);
            settype($number, "double");

            if(array_search("zero_or_greater", $params) !== false) {
                if($number >= 0) {
                    return true;
                } else {
                    $this->error->set($msg);
                    return false;
                }
            } elseif(array_search("greater_than_zero", $params) !== false) {
                if($number > 0) {
                    return true;
                } else {
                    $this->error->set($msg);
                    return false;
                }
            } else {
                return true;
            }
        } else {
            $this->error->set($msg);
            return false;
        }
    }

    /**
     * Validates as identifier, eg. in an url
     *
     * @param string $string To validate
     * @param string $msg    Error msg
     * @param string $param  Extra parameters
     *
     * @return boolean
     */
    function isIdentifier($string, $msg, $param = '')
    {
        $params = explode(",", $param);
        if (array_search('allow_empty', $params) !== false && empty($string)) {
            return true;
        } elseif (eregi("^[a-z0-9_-]+$", $string)) {
            return true;
        }
        return false;
    }
}

?>