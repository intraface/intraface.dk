<?php
function isAjax() {
	if (!empty($_REQUEST['ajax']) AND $_REQUEST['ajax'] == true) {
		return 1;
	}

	if (!empty($_SERVER['HTTP_ACCEPT']) AND $_SERVER['HTTP_ACCEPT'] == 'message/x-jl-formresult') {
		return 1;
	}

	if (!empty($_SERVER['X-Requested-With']) AND $_SERVER['X-Requested-With'] == 'XMLHttpRequest') {
		return 1;
	}

	return 0;
}



/**
 * Funktion til at outputte et beløb landespecifik notation
 * Det kunne jo være gavnligt om metoden også indeholdte noget om,
 * hvilket land der er tale om.
 */

function amountToOutput($amount) {
	return number_format($amount, 2, ',', '.');
}

/**
 * Funktion til at outputte et beløb landespecifik notation i en formular
 */

function amountToForm($amount) {
	return number_format($amount, 2, ',', '');
}

/**
 * Funktion til at konvertere beløb så de kan gemmes i databasen
 *
 * Funktionen skal konvertere til den mindste enhed af beløbet
 * i vores tilfælde ofte ører
 */
function amountToDb($amount) {
	## dette konverterer fra dansk til engelsk format - men så bør den også være landespecifik
	## spørgsmålet er hvordan vi gør dem landespecifikke på en smart måde?
	$amount = str_replace(".", "", $amount);
	$amount = str_replace(",", ".", $amount);

	return $amount;

}


function dateToOutput() {
}

function dateToDb() {
}


function autoop($text) {
	require_once 'wordpress/functions-formatting.php';
	require_once 'Markdown/markdown.php';
	require_once 'SmartyPants/smartypants.php';

	$text = MarkDown($text);
	$text = SmartyPants($text);
	return wpautop($text); // wordpress function
}

function email($email) {
	require_once '3Party/wordpress/functions-formatting.php';
	return antispambot($email); // wordpress function
}

function autoclicable($string) {
	require_once '3Party/wordpress/functions-formatting.php';
	return make_clickable($string); // wordpress function
}

function scramble_cpr($cpr) {
	return substr($cpr,0,-4) . '-xxxx';
}

function handle_microsoft($text) {
	$text = str_replace(chr(145), "'", $text);
	$text = str_replace(chr(146), "'", $text);
	$text = str_replace(chr(147), '"', $text);
	$text = str_replace(chr(148), '"', $text);
	$text = str_replace(chr(148), '"', $text);
	$text = str_replace(chr(150), '-', $text);
	$text = str_replace(chr(151), '--', $text);
	$text = str_replace(chr(133), '...', $text);
	return $text;
}

  /**
  * Translate literal entities to their numeric equivalents and vice versa.
  *
  * PHP's XML parser (in V 4.1.0) has problems with entities! The only one's that are recognized
  * are &amp;, &lt; &gt; and &quot;. *ALL* others (like &nbsp; &copy; a.s.o.) cause an
  * XML_ERROR_UNDEFINED_ENTITY error. I reported this as bug at http://bugs.php.net/bug.php?id=15092
  * The work around is to translate the entities found in the XML source to their numeric equivalent
  * E.g. &nbsp; to &#160; / &copy; to &#169; a.s.o.
  *
  * NOTE: Entities &amp;, &lt; &gt; and &quot; are left 'as is'
  *
  * @author Sam Blum bs_php@users.sourceforge.net
  * @param string $xmlSource The XML string
  * @param bool   $reverse (default=FALSE) Translate numeric entities to literal entities.
  * @return The XML string with translatet entities.
  */
  function _translateLiteral2NumericEntities($xmlSource, $reverse = FALSE) {
    static $literal2NumericEntity;

    if (empty($literal2NumericEntity)) {
      $transTbl = get_html_translation_table(HTML_ENTITIES);
      foreach ($transTbl as $char => $entity) {
        if (strpos('&"<>', $char) !== FALSE) continue;
        $literal2NumericEntity[$entity] = '&#'.ord($char).';';
      }
    }
    if ($reverse) {
      return strtr($xmlSource, array_flip($literal2NumericEntity));
    } else {
      return strtr($xmlSource, $literal2NumericEntity);
    }
  }


if(!function_exists('mime_content_type')) {
	// mime_content_type først fra PHP 4.3
	// Taget fra http://dk.php.net/manual/en/function.mime-content-type.php
	function mime_content_type($f) {
		return exec(trim('file -bi '.escapeshellarg($f)));
	}
}

/**
 * Function to be called before putting data in the database
 *
 * @author	Lars Olesen <lars@legestue.net>
 */
function safeToDb($data) {
	if(is_array($data)){
		return array_map('safeToDb',$data);
	}

	if (get_magic_quotes_gpc()) {
		$data = stripslashes($data);
	}

	return mysql_escape_string(trim($data));
}

/**
 * Function to be called before outputting data to a form
 *
 * @author	Lars Olesen <lars@legestue.net>
 */
function safeToForm($data) {

	// return 'safeToForm'; // for debugging of use of safeToForm

	return safeToHtml($data);


}

/**
 * Function to be called before putting data into a form
 *
 * Metoden skal i øvrigt skrives om hvis den skal fungere sådan her til den
 * der findes i vores subversion.
 *
 * @author	Lars Olesen <lars@legestue.net>
 */
function safeToHtml($data) {
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


function safe_url() {
}


/**
 * Method to get information from either GET, POST, SESSION OR COOKIE
 * Question is if it is necessary - we are performing safe_to_db() other places.
 *
 * @author	Lars Olesen <lars@legestue.net>
 */
function safe_from_request($data) {
	die('Ikke implementeret');
	/*
	if (get_magic_quotes_gpc()) {
		$data = stripslashes($data);
	}
    if ($_SERVER['REQUEST_METHOD'] == 'GET')
        return strip_tags($_GET[$name]);
    if ($_SERVER['REQUEST_METHOD'] == 'POST')
        return strip_tags($_POST[$name]);
*/

	// skal get urldecodes?

	// skal nok være rekursiv

       /* remove backslashes if some annoying
           antique nosy ini setting has put them in...
           I made only this one by reference, cause
           you shouldn't want to do anything with
           the input if they MAY contain abundant backslashes
       */
}

/*************

 FØLGENDE SKAL UD AF SYSTEMET

*******************/



/**
 * @author Lars Olesen <lars@legestue.net>
 */

function safeEscapeString (& $input) {
	echo 'safeEscapeString er blevet bedre. Derfor skal denne ændres.';
	var_dump(debug_backtrace());
	return safeToDb($input);
	/*
	if (!get_magic_quotes_gpc()) {
		foreach ($input as $k=>$v) {
			if (is_array($v)) {
				$input[$k] = safeEscapeString($v);
			}
			else {
				$input[$k] = mysql_escape_string($v);
			}
		}
	}
	else {
		return $input;
	}
	*/
}

/**
 * @author Lars Olesen <lars@legestue.net>
 */

function safeStripSlashes(&$input) {
	echo 'safeStripSlashes: Hvis du render på mig, skal du lige fjerne mig, for jeg skal ikke bruges længere';
	if (!get_magic_quotes_gpc()) {
		foreach ($input as $k=>$v) {
			if (is_array($v)) {
				$input[$k] = safeStripSlashes($v);
			}
			else {
				$input[$k] = stripslashes($v);
			}
		}

	}
	else {
		return $input;
	}
}

?>