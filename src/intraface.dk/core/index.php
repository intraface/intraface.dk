<?php
// common settings
define('INTRAFACE_K2', true);

require_once dirname(__FILE__) . '/../config.local.php';
ini_set('include_path', PATH_INCLUDE_PATH);
require_once dirname(__FILE__) . '/../common.php';
require_once 'Ilib/ClassLoader.php';
require_once 'konstrukt/konstrukt.inc.php';
//set_error_handler('k_exceptions_error_handler');
spl_autoload_register('k_autoload');


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
        $__template_filename__ = self::searchIncludePath($filename);
        if (!is_file($__template_filename__)) {
            throw new Exception("Failed opening '".$filename."' for inclusion. (include_path=".ini_get('include_path').")");
        }
        return new k_Template($__template_filename__);
    }

    /**
     * Searches the include-path for a filename.
     * Returns the absolute path (realpath) if found or FALSE
     * @return mixed
     */
    static function SearchIncludePath($filename) {
        if (is_file($filename)) {
            return $filename;
        }
        foreach (explode(PATH_SEPARATOR, ini_get("include_path")) as $path) {
            if (strlen($path) > 0 && $path{strlen($path)-1} != DIRECTORY_SEPARATOR) {
                $path .= DIRECTORY_SEPARATOR;
            }
            $f = realpath($path . $filename);
            if ($f && is_file($f)) {
                return $f;
            }
        }
        return FALSE;
    }
}

$components = new k_InjectorAdapter($bucket, new Intraface_Document);
$components->setImplementation('k_DefaultNotAuthorizedComponent', 'NotAuthorizedComponent');

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
