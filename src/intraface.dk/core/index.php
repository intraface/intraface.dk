<?php
// common settings
define('INTRAFACE_K2', true);



/**
 * An error-handler which converts all errors (regardless of level) into exceptions.
 * It respects error_reporting settings.
 */
function intraface_exceptions_error_handler($severity, $message, $filename, $lineno) {
  if (error_reporting() == 0) {
    return;
  }
  if (error_reporting() && $severity) {
      if ($severity == 8 or $severity = 20482048) {
          return;
      }
    throw new ErrorException($message, 0, $severity, $filename, $lineno);
  }
}


require_once dirname(__FILE__) . '/../config.local.php';
ini_set('include_path', PATH_INCLUDE_PATH);
require_once dirname(__FILE__) . '/../common.php';
require_once 'Ilib/ClassLoader.php';
require_once 'konstrukt/konstrukt.inc.php';
//set_error_handler('k_exceptions_error_handler');
spl_autoload_register('k_autoload');
error_reporting(E_ALL);

set_error_handler('intraface_exceptions_error_handler');


class k_PdfResponse extends k_ComplexResponse
{
    function contentType()
    {
        return 'application/pdf';
    }

    protected function marshal()
    {
        return $this->content;
    }
}


class Intraface_AuthenticatedUser extends k_AuthenticatedUser
{
    protected $language;
    function __construct($name, k_Language $lang)
    {
        $this->language = $lang;
        parent::__construct($name);
    }

    function language()
    {
        return $this->language;
    }
}

// session_start();

class DanishLanguage implements k_Language {
    function name() {
        return 'Danish';
    }
    function isoCode() {
        return 'da';
    }
}

class EnglishLanguage implements k_Language {
    function name() {
        return 'English';
    }
    function isoCode() {
        return 'uk';
    }
}

class Intraface_LanguageLoader implements k_LanguageLoader {
    // @todo The language will often not be set on runtime, e.g. an
    //       intranet where the user can chose him or her own language?
    //       How could one accommodate for this?
    function load(k_Context $context) {
        require_once 'PEAR.php';
        require_once 'HTTP.php';

        if ($context->identity()->anonymous()) {
            $supported = array("da" => true, "en-US" => true);
            $language = HTTP::negotiateLanguage($supported);
            if (PEAR::isError($language)) {
                // fallback language in case of unable to negotiate
                return new DanishLanguage();
            }

            if ($language == 'en-US') {
                return new EnglishLanguage();
            }

        } elseif ($context->identity()->language() == 'da') {
            return new DanishLanguage();
        }

        return new EnglishLanguage();
    }
}

class k_Translation2Translator implements k_Translator {
    protected $translation2;
    function __construct($lang) {
        $factory = new Intraface_Factory;
        $this->translation2 = $factory->new_Translation2();
        $res = $this->translation2->setLang($lang);
        if (PEAR::isError($res)) {
            throw new Exception('Could not setLang()');
        }
    }

    function translate($phrase, k_Language $language = null) {
        // Translation2 groups translations with pageID(). This can
        // be accommodated like this
        if (is_array($phrase) && count($phrase) == 2) {
            return $this->translation2->get($phrase[0], $phrase[1]);
        }
        return $this->translation2->get($phrase, 'basic');
    }
}

class Intraface_TranslatorLoader implements k_TranslatorLoader {
    function load(k_Context $context) {
        return new k_Translation2Translator($context->language()->isoCode());
    }
}

class Intraface_IdentityLoader implements k_IdentityLoader
{
    function load(k_Context $context) {
        if ($context->session('intraface_identity')) {
            return $context->session('intraface_identity');
        }
        return new k_Anonymous();
    }
}

class NotAuthorizedComponent extends k_Component {
    function dispatch() {
        // redirect to login-page
        return new k_TemporaryRedirect($this->url('/login', array('continue' => $this->requestUri())));
    }
}

class Intraface_Document extends k_Document
{
    public $options;
    function options()
    {
        if (empty($this->options)) return array();
        return $this->options;
    }
}

class Intraface_TemplateFactory extends k_DefaultTemplateFactory
{
    function create($filename)
    {
        $filename = $filename . '.tpl.php';
        $__template_filename__ = k_search_include_path($filename);
        if (!is_file($__template_filename__)) {
            throw new Exception("Failed opening '".$filename."' for inclusion. (include_path=".ini_get('include_path').")");
        }
        return new k_Template($__template_filename__);
    }
}

$components = new k_InjectorAdapter($bucket, new Intraface_Document);
$components->setImplementation('k_DefaultNotAuthorizedComponent', 'NotAuthorizedComponent');

if (realpath($_SERVER['SCRIPT_FILENAME']) == __FILE__) {
    try {
        k()
        // Use container for wiring of components
        ->setComponentCreator($components)
        // Enable file logging
        ->setLog(K2_LOG)
        // Uncomment the next line to enable in-browser debugging
        //->setDebug(K2_DEBUG)
        // Dispatch request
        ->setIdentityLoader(new Intraface_IdentityLoader())
        ->setLanguageLoader(new Intraface_LanguageLoader())
        ->setTranslatorLoader(new Intraface_TranslatorLoader())
        ->run('Intraface_Controller_Index')
        ->out();
    } catch (ErrorException $e) {

        // hvordan granulerer man når der nu bliver smidt en exception altid?
        // vi kan sikkert køre out, når vi har lyst - og exception bliver
        // kastet inden out?

        $render = new Ilib_Errorhandler_Handler_File(Log::factory('file', './error.log', 'INTRAFACE'));
        $render->handle($e);

        if ($e->getSeverity() > 8) {
            $render = new Ilib_Errorhandler_Handler_Echo();
            $render->handle($e);
        }
    }
}