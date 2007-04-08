<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Contains the Translation2_Decorator_TriggerError class
 *
 * PHP versions 4 and 5
 *
 * LICENSE: Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. The name of the author may not be used to endorse or promote products
 *    derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR "AS IS" AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE FREEBSD PROJECT OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
 * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Internationalization
 * @package    Translation2
 * @author     Sune Jensen (sune at intraface dot dk)
 * @copyright
 * @license
 * @version    0.1
 * @link
 */

/**
 * Load Translation2 decorator base class
 */
require_once 'Translation2/Decorator.php';

/**
 * Decorator to provide a fallback text for empty strings.
 *
 * @category   Internationalization
 * @package    Translation2
 * @author     Lorenzo Alberton <l dot alberton at quipo dot it>
 * @copyright  2004-2005 Lorenzo Alberton
 * @license    http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @version    CVS: $Id: ErrorText.php,v 1.4 2005/09/08 17:27:37 quipo Exp $
 * @link       http://pear.php.net/package/Translation2
 */
class Translation2_Decorator_LogMissingTranslation extends Translation2_Decorator
{
    // {{{ get()
	// @todo should be set outside class
    var $error_log = ERROR_LOG;

    /**
     * Get translated string
     *
     * If the string is empty, trigger an E_USER_NOTICE
     *
     * @param string $stringID
     * @param string $pageID
     * @param string $langID
     * @param string $defaultText Text to display when the string is empty
     * @return string
     */
    function get($stringID, $pageID=TRANSLATION2_DEFAULT_PAGEID, $langID=null, $defaultText='')
    {
        $str = $this->translation2->get($stringID, $pageID, $langID, $defaultText);
        if (empty($str)) {
            $this->writeLog('Missing translation for "'.$stringID.'" on pageID: "'.$pageID.'", LangID: "'.$langID.'"');
        }
        return $str;
    }

    // }}}
    // {{{ getPage()

    /**
     * Same as getRawPage, but resort to fallback language and
     * replace parameters when needed
     *
     * @param string $pageID
     * @param string $langID
     * @return array
     */
    /*
    function getPage($pageID=TRANSLATION2_DEFAULT_PAGEID, $langID=null)
    {
        $data = $this->translation2->getPage($pageID, $langID);
        $error_text = str_replace('"', '\"', $this->translation2->getLang(null, 'error_text'));

        array_walk(
            $data,
            create_function('$str, $stringID', 'trigger_error(\'Missing translation for stringID "\'.$stringID.\'", pageID: "'.$pageID.'", LangID: "'.$langID.'"\', E_USER_NOTICE);')
        );


        return $data;
    }
	*/
    // }}}

    function writeLog($err) {
    	$error = array(
			'date' => date('r'),
			'type' => 'Translation2',
			'message' => $err,
			'file' => '',
			'line' => '',
			'request' => $_SERVER['REQUEST_URI']
		);

		if (!isset($this->error_log)) {
			return PEAR::raiseError('Error log is not set');
		}
		if(touch($this->error_log)) {
			file_put_contents($this->error_log, serialize($error) . "\n", FILE_APPEND);
		}
    }

}
?>
