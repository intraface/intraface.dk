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

// e()
if (!function_exists('e')) {
    /**
     * This function is dynamically redefinable.
     * @see $GLOBALS['_global_function_callback_e']
     */
    function e($args) 
    {
        $args = func_get_args();
        return call_user_func_array($GLOBALS['_global_function_callback_e'], $args);
    }
    if (!isset($GLOBALS['_global_function_callback_e'])) {
        $GLOBALS['_global_function_callback_e'] = NULL;
    }
}

$GLOBALS['_global_function_callback_e'] = 'intraface_e';

function intraface_e($string)
{
    echo htmlentities($string);
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
 * Funktion til at outputte et beløb landespecifik notation
 * Det kunne jo være gavnligt om metoden også indeholdte noget om,
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
 * Funktion til at outputte et beløb landespecifik notation i en formular
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
 * Funktion til at konvertere beløb så de kan gemmes i databasen
 *
 * Funktionen skal konvertere til den mindste enhed af beløbet
 * i vores tilfælde ofte ører
 */
function intraface_amountToDb($amount) 
{
    // dette konverterer fra dansk til engelsk format - men så bør den også være landespecifik
    // spørgsmålet er hvordan vi gør dem landespecifikke på en smart måde?
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
    return $text;
}

$GLOBALS['_global_function_callback_autoop'] = 'intraface_autoop';

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
    if(is_object($data)) {
        return $data;
    }
    
    if(is_array($data)){
        return array_map('safeToDb',$data);
    }

    if (get_magic_quotes_gpc()) {
        $data = stripslashes($data);
    }

    return mysql_escape_string(trim($data));
}

// Dynamic global functions
if (!function_exists('safeToForm')) {
    /**
     * This function is dynamically redefinable.
     * @see $GLOBALS['_global_function_callback_e']
     */
    function safeToForm($args) 
    {
        $args = func_get_args();
        return call_user_func_array($GLOBALS['_global_function_callback_safetoform'], $args);
    }
    if (!isset($GLOBALS['_global_function_callback_safetoform'])) {
        $GLOBALS['_global_function_callback_safetoform'] = NULL;
    }
}
$GLOBALS['_global_function_callback_safetoform'] = 'intraface_safetoform';

/**
 * Function to be called before outputting data to a form
 *
 * @author	Lars Olesen <lars@legestue.net>
 */
function intraface_safetoform($data) 
{
    return safeToHtml($data);
}

// Dynamic global functions
if (!function_exists('safeToHtml')) {
    /**
     * This function is dynamically redefinable.
     * @see $GLOBALS['_global_function_callback_e']
     */
    function safeToHtml($args) 
    {
        $args = func_get_args();
        return call_user_func_array($GLOBALS['_global_function_callback_safetohtml'], $args);
    }
    if (!isset($GLOBALS['_global_function_callback_safetohtml'])) {
        $GLOBALS['_global_function_callback_safetohtml'] = NULL;
    }
}
$GLOBALS['_global_function_callback_safetohtml'] = 'intraface_safeToHtml';

/**
 * Function to be called before putting data into a form
 *
 * Metoden skal i øvrigt skrives om hvis den skal fungere sådan her til den
 * der findes i vores subversion.
 *
 * @author	Lars Olesen <lars@legestue.net>
 */
function intraface_safeToHtml($data) 
{
    // denne bruges i forbindelse med translation - kan sikkert fjernes når alt er implementeret
    if (is_object($data)) return $data->getMessage();

    // egentlig bør den her vel ikke være rekursiv. Man skal kun bruge den når man skriver direkte ud.
    if(is_array($data)){
        return array_map('safeToHtml',$data);
    }

    if (get_magic_quotes_gpc()) {
        $data = stripslashes($data);
    }

    // return 'safeToHtml'; // For debugging of use of safeToHtml
    return htmlspecialchars($data);
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