<?php
// isAjax()
if (!function_exists('isAjax')) {
    /**
     * This function is dynamically redefinable.
     * @see $GLOBALS['_global_function_callback_e']
     */
    function isAjax($args = NULL)
    {
        $args = func_get_args();
        return call_user_func_array($GLOBALS['_global_function_callback_isAjax'], $args);
    }
    if (!isset($GLOBALS['_global_function_callback_isAjax'])) {
        $GLOBALS['_global_function_callback_isAjax'] = NULL;
    }
}

$GLOBALS['_global_function_callback_isAjax'] = 'intraface_isAjax';

function intraface_isAjax()
{
    if (!empty($_REQUEST['ajax']) AND $_REQUEST['ajax'] == true) {
        return true;
    }

    if (!empty($_SERVER['HTTP_ACCEPT']) AND $_SERVER['HTTP_ACCEPT'] == 'message/x-jl-formresult') {
        return true;
    }

    if (!empty($_SERVER['X-Requested-With']) AND $_SERVER['X-Requested-With'] == 'XMLHttpRequest') {
        return true;
    }

    return false;
}

// amountToOutput
if (!function_exists('amountToOutput')) {
    /**
     * This function is dynamically redefinable.
     * @see $GLOBALS['_global_function_callback_e']
     */
    function amountToOutput($args)
    {
        $args = func_get_args();
        return call_user_func_array($GLOBALS['_global_function_callback_amountToOutput'], $args);
    }
    if (!isset($GLOBALS['_global_function_callback_amountToOutput'])) {
        $GLOBALS['_global_function_callback_amountToOutput'] = NULL;
    }
}

$GLOBALS['_global_function_callback_amountToOutput'] = 'intraface_AmountToOutput';

/**
 * Funktion til at outputte et bel�b landespecifik notation
 * Det kunne jo v�re gavnligt om metoden ogs� indeholdte noget om,
 * hvilket land der er tale om.
 */
function amountToOutput($amount)
{
    return number_format($amount, 2, ',', '.');
}

// amountToForm
if (!function_exists('amountToForm')) {
    /**
     * This function is dynamically redefinable.
     * @see $GLOBALS['_global_function_callback_e']
     */
    function amountToForm($args)
    {
        $args = func_get_args();
        return call_user_func_array($GLOBALS['_global_function_callback_amountToForm'], $args);
    }
    if (!isset($GLOBALS['_global_function_callback_amountToForm'])) {
        $GLOBALS['_global_function_callback_amountToForm'] = NULL;
    }
}

$GLOBALS['_global_function_callback_amountToForm'] = 'intraface_AmountToForm';


/**
 * Funktion til at outputte et bel�b landespecifik notation i en formular
 */
function intraface_amountToForm($amount)
{
    return number_format($amount, 2, ',', '');
}

// amountToDb
if (!function_exists('amountToDb')) {
    /**
     * This function is dynamically redefinable.
     * @see $GLOBALS['_global_function_callback_e']
     */
    function amountToDb($args)
    {
        $args = func_get_args();
        return call_user_func_array($GLOBALS['_global_function_callback_amountToDb'], $args);
    }
    if (!isset($GLOBALS['_global_function_callback_amountToDb'])) {
        $GLOBALS['_global_function_callback_amountToDb'] = NULL;
    }
}

$GLOBALS['_global_function_callback_amountToDb'] = 'intraface_AmountToDb';


/**
 * Funktion til at konvertere bel�b s� de kan gemmes i databasen
 *
 * Funktionen skal konvertere til den mindste enhed af bel�bet
 * i vores tilf�lde ofte �rer
 */
function intraface_amountToDb($amount)
{
    // dette konverterer fra dansk til engelsk format - men s� b�r den ogs� v�re landespecifik
    // sp�rgsm�let er hvordan vi g�r dem landespecifikke p� en smart m�de?
    $amount = str_replace(".", "", $amount);
    $amount = str_replace(",", ".", $amount);

    return $amount;
}

// autoop()
if (!function_exists('autoop')) {
    /**
     * This function is dynamically redefinable.
     * @see $GLOBALS['_global_function_callback_email']
     */
    function autoop($args) {
        $args = func_get_args();
        return call_user_func_array($GLOBALS['_global_function_callback_autoop'], $args);
    }
    if (!isset($GLOBALS['_global_function_callback_autoop'])) {
        $GLOBALS['_global_function_callback_autoop'] = NULL;
    }
}

function intraface_autoop($text)
{
    require_once 'markdown.php';
    require_once 'smartypants.php';

    $text = MarkDown($text);
    $text = SmartyPants($text);
    return utf8_decode($text);
}

$GLOBALS['_global_function_callback_autoop'] = 'intraface_autoop';

// autoop()
if (!function_exists('autohtml')) {
    /**
     * This function is dynamically redefinable.
     * @see $GLOBALS['_global_function_callback_email']
     */
    function autohtml($args) {
        $args = func_get_args();
        return call_user_func_array($GLOBALS['_global_function_callback_autohtml'], $args);
    }
    if (!isset($GLOBALS['_global_function_callback_autohtml'])) {
        $GLOBALS['_global_function_callback_autohtml'] = NULL;
    }
}

function intraface_autohtml($text)
{
    require_once 'markdown.php';
    require_once 'smartypants.php';

    $text = nl2br($text);
    $text = MarkDown($text);
    $text = SmartyPants($text);
    echo utf8_decode($text);
}

$GLOBALS['_global_function_callback_autohtml'] = 'intraface_autohtml';


// Dynamic global functions
if (!function_exists('safeToDb')) {
    /**
     * This function is dynamically redefinable.
     * @see $GLOBALS['_global_function_callback_e']
     */
    function safeToDb($args)
    {
        $args = func_get_args();
        return call_user_func_array($GLOBALS['_global_function_callback_safetodb'], $args);
    }
    if (!isset($GLOBALS['_global_function_callback_safetodb'])) {
        $GLOBALS['_global_function_callback_safetodb'] = NULL;
    }
}
$GLOBALS['_global_function_callback_safetodb'] = 'intraface_safetodb';

/**
 * Function to be called before putting data in the database
 *
 * @author  Lars Olesen <lars@legestue.net>
 */
function intraface_safetodb($data)
{
    if (is_object($data)) {
        return $data;
    }

    if (is_array($data)){
        return array_map('safeToDb',$data);
    }

    if (get_magic_quotes_gpc()) {
        $data = stripslashes($data);
    }

    return mysql_escape_string(trim($data));
}

//Dynamic global functions
if (!function_exists('filesize_readable')) {
    /**
     * This function is dynamically redefinable.
     * @see $GLOBALS['_global_function_callback_e']
     */
    function filesize_readable($args)
    {
        $args = func_get_args();
        return call_user_func_array($GLOBALS['_global_function_callback_filesize_readable'], $args);
    }
    if (!isset($GLOBALS['_global_function_callback_filesize_readable'])) {
        $GLOBALS['_global_function_callback_filesize_readable'] = NULL;
    }
}
$GLOBALS['_global_function_callback_filesize_readable'] = 'intraface_filesize_readable';

/*
 * Function to convert filesize to readable sizes.
 * from: http://us3.php.net/filesize
 */
function intraface_filesize_readable($size, $retstring = null)
{
        // adapted from code at http://aidanlister.com/repos/v/function.size_readable.php
        $sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        if ($retstring === null) { $retstring = '%01.2f %s'; }
        $lastsizestring = end($sizes);
        foreach ($sizes as $sizestring) {
                if ($size < 1024) { break; }
                if ($sizestring != $lastsizestring) { $size /= 1024; }
        }
        if ($sizestring == $sizes[0]) { $retstring = '%01d %s'; } // Bytes aren't normally fractional
        return sprintf($retstring, $size, $sizestring);
}

function intrafaceBackendErrorhandler($errno, $errstr, $errfile, $errline, $errcontext)
{
    if (!defined('ERROR_LOG')) define('ERROR_LOG', dirname(__FILE__) . '/../log/error.log');
    $errorhandler = new ErrorHandler;
    if (!defined('ERROR_LEVEL_CONTINUE_SCRIPT')) define('ERROR_LEVEL_CONTINUE_SCRIPT', E_ALL);
    $errorhandler->addObserver(new ErrorHandler_Observer_File(ERROR_LOG));
    $errorhandler->addObserver(new ErrorHandler_Observer_Echo, ~ ERROR_LEVEL_CONTINUE_SCRIPT); // From php.net "~ $a: Bits that are set in $a are not set, and vice versa." That means the observer is used on everything but ERROR_LEVEL_CONTINUE_SCRIPT
    return $errorhandler->handleError($errno, $errstr, $errfile, $errline, $errcontext);
}

function intrafaceBackendExceptionhandler($e)
{
    $errorhandler = new ErrorHandler;
    $errorhandler->addObserver(new ErrorHandler_Observer_File(ERROR_LOG));
    $errorhandler->addObserver(new ErrorHandler_Observer_Echo);
    return $errorhandler->handleException($e);
}

function intrafaceFrontendErrorhandler($errno, $errstr, $errfile, $errline, $errcontext)
{
    $errorhandler = new ErrorHandler;
    $errorhandler->addObserver(new ErrorHandler_Observer_File(ERROR_LOG));
    if (defined('SERVER_STATUS') && SERVER_STATUS == 'TEST') {
        $errorhandler->addObserver(new ErrorHandler_Observer_BlueScreen, ~ ERROR_LEVEL_CONTINUE_SCRIPT); // From php.net "~ $a: Bits that are set in $a are not set, and vice versa." That means the observer is used on everything but ERROR_LEVEL_CONTINUE_SCRIPT
    } else {
        $errorhandler->addObserver(new ErrorHandler_Observer_User, ~ ERROR_LEVEL_CONTINUE_SCRIPT); // See description of ~ above
    }
    return $errorhandler->handleError($errno, $errstr, $errfile, $errline, $errcontext);
}

function intrafaceFrontendExceptionhandler($e)
{
    $errorhandler = new ErrorHandler;
    $errorhandler->addObserver(new ErrorHandler_Observer_File(ERROR_LOG));
    if (defined('SERVER_STATUS') && SERVER_STATUS == 'TEST') {
        $errorhandler->addObserver(new ErrorHandler_Observer_BlueScreen);
    } else {
        $errorhandler->addObserver(new ErrorHandler_Observer_User);
    }
    return $errorhandler->handleException($e);
}
