<?php
require_once 'config.local.php';

// NOTICE: session_start is needed to be executed before Classloader is initialized.
// Otherwise it gives strange error trying to start MDB2_Driver_mysql

session_start();

require_once 'konstrukt/konstrukt.inc.php';
require_once 'bucket.inc.php';
require_once 'Ilib/ClassLoader.php';

class EnglishLanguage implements k_Language
{
  function name()
  {
    return 'English';
  }

  function isoCode()
  {
    return 'en';
  }
}

class MyLanguageLoader implements k_LanguageLoader
{
  function load(k_Context $context)
  {
    if($context->query('lang') == 'en') {
      return new EnglishLanguage();
    }
    return new EnglishLanguage();
  }
}

class SimpleTranslator implements k_Translator
{
  protected $phrases;

  function __construct($phrases = array())
  {
    $this->phrases = $phrases;
  }

  function translate($phrase, k_Language $language = null)
  {
    return isset($this->phrases[$phrase]) ? $this->phrases[$phrase] : $phrase;
  }
}

class SimpleTranslatorLoader implements k_TranslatorLoader
{
  function load(k_Context $context) {
    // Default to English
    $phrases = array(
      'Hello' => 'Hello',
      'Meatballs' => 'Meatballs',
    );
    if($context->language()->isoCode() == 'sv') {
      $phrases = array(
        'Hello' => 'Bork, bork, bork!',
        'Meatballs' => 'Swedish meatballs',
      );
    }
    return new SimpleTranslator($phrases);
  }
}

class Demo_TemplateFactory extends k_DefaultTemplateFactory
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


class Intraface_Demo_Factory
{
    function new_IntrafacePublic_Admin_Client_XMLRPC()
    {
        return new IntrafacePublic_Admin_Client_XMLRPC($GLOBALS["masterpassword"], false, INTRAFACE_XMLPRC_SERVER_PATH . "admin/server.php");
    }

    function new_Cache_Lite()
    {
        $options = array(
            "cacheDir" => PATH_CACHE,
            "lifeTime" => 3600
        );
        return new Cache_Lite($options);
    }

    function new_IntrafacePublic_Frontend_Translation()
    {
        $options = array(
            "da" => true,
            "en" => true
        );

        $language = HTTP::negotiateLanguage($options, "en");

        $translation = IntrafacePublic_Frontend_Translation::factory($language);
        $translation->setPageID("kundelogin");
        return $translation;
    }

    function new_k_TemplateFactory()
    {
        return new Demo_TemplateFactory(dirname(__FILE__));
    }
}

class Demo_Document extends k_Document
{
    public $style;
    public $keywords;
    public $description;
    public $navigation = array();

    function locale()
    {
        return 'enus';
    }

    function setCurrentStep($step)
    {

    }

    function currentStep()
    {

    }

    function purchaseSteps()
    {
        return array();
    }

    function companyName()
    {
        return 'Intraface';
    }

    function menu()
    {

    }
}

$bucket = new bucket_Container(new Intraface_Demo_Factory());
$components = new k_InjectorAdapter($bucket, new Demo_Document);

if (realpath($_SERVER['SCRIPT_FILENAME']) == __FILE__) {
    k()
    // Use container for wiring of components
    ->setComponentCreator($components)
    // Enable file logging
    //->setLog(K2_LOG)
    // Uncomment the next line to enable in-browser debugging
    //->setDebug(K2_DEBUG)
    // Dispatch request
    //->setIdentityLoader(new Intraface_IdentityLoader())
    ->setLanguageLoader(new MyLanguageLoader())
    ->setTranslatorLoader(new SimpleTranslatorLoader())
    ->run('Demo_Root')
    ->out();
}