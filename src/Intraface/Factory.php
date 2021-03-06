<?php
class Intraface_Factory
{
    protected $config;

    function __construct($config = null)
    {
        $this->config = $config;
    }

    function new_k_TemplateFactory($c)
    {
        return new Intraface_TemplateFactory(null);
    }

    function new_MDB2($c)
    {
        return $this->new_MDB2_Driver_Common($c);
    }

    function new_MDB2_Driver_Common($container)
    {
        $db = MDB2::singleton(DB_DSN, array('persistent' => true));
        if (PEAR::isError($db)) {
            throw new Exception($db->getMessage() . $db->getUserInfo());
        }

        $db->setFetchMode(MDB2_FETCHMODE_ASSOC);
        $db->query('SET NAMES utf8');
        $res = $db->setCharset('utf8');

        $db->setOption('debug', MDB2_DEBUG);
        $db->setOption('portability', MDB2_PORTABILITY_NONE);

        if (PEAR::isError($res)) {
            throw new Exception($res->getUserInfo());
        }

        if ($db->getOption('debug')) {
            $db->setOption('log_line_break', "\n\n\n\n\t");

            $my_debug_handler = new MDB2_Debug_ExplainQueries($db);
            $db->setOption('debug_handler', array($my_debug_handler, 'collectInfo'));

            register_shutdown_function(array($my_debug_handler, 'executeAndExplain'));
            register_shutdown_function(array($my_debug_handler, 'dumpInfo'));
        }

        return $db;
    }

    function new_DB_Sql($container)
    {
        $db = new DB_Sql(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $db->query('SET NAMES utf8');
        return $db;
    }

    function new_Translation2()
    {
        // set the parameters to connect to your db
        $dbinfo = array(
            'hostspec' => DB_HOST,
            'database' => DB_NAME,
            'phptype'  => 'mysql',
            'username' => DB_USER,
            'password' => DB_PASS
        );

        if (!defined('LANGUAGE_TABLE_PREFIX')) {
            define('LANGUAGE_TABLE_PREFIX', 'core_translation_');
        }

        $params = array(
            'langs_avail_table' => LANGUAGE_TABLE_PREFIX.'langs',
            'strings_default_table' => LANGUAGE_TABLE_PREFIX.'i18n'
        );

        $translation = Translation2::factory('MDB2', $dbinfo, $params);
        //always check for errors. In this examples, error checking is omitted
        //to make the example concise.
        if (PEAR::isError($translation)) {
            throw new Exception('Could not start Translation ' . $translation->getMessage());
        }

        // set the group of strings you want to fetch from
        // $translation->setPageID($page_id);

        // add a Lang decorator to provide a fallback language
        // $translation = $translation->getDecorator('Lang');
        // $translation->setOption('fallbackLang', 'uk');
        // $translation = $translation->getDecorator('LogMissingTranslation');
        // $translation->setOption('logger', array(new ErrorHandler_Observer_File(ERROR_LOG), 'update'));
        $translation = $translation->getDecorator('DefaultText');

        // %stringID% will be replaced with the stringID
        // %pageID_url% will be replaced with the pageID
        // %stringID_url% will replaced with a urlencoded stringID
        // %url% will be replaced with the targeted url
        //$this->translation->outputString = '%stringID% (%pageID_url%)'; //default: '%stringID%'
        $translation->outputString = '%stringID%';
        $translation->url = '';           //same as default
        $translation->emptyPrefix  = '';  //default: empty string
        $translation->emptyPostfix = '';  //default: empty string
        return $translation;
    }

    function new_Translation2_Cache()
    {
        $options = array(
            "cacheDir" => PATH_CACHE.'translation/',
            "lifeTime" => 3600
        );
        return new Cache_Lite($options);
    }

    function new_Intraface_Auth($container)
    {
        return new Intraface_Auth(session_id());
    }

    function new_Doctrine_Connection_Common()
    {
        $connection = Doctrine_Manager::connection(DB_DSN);
        $connection->setCharset('utf8');
        return $connection;
    }

    function new_Swift_Message($c)
    {
        return Swift_Message::newInstance();
    }

    function new_Swift_Mailer($c)
    {
        return Swift_Mailer::newInstance($this->new_Swift_Transport($c));
    }

    function new_Swift_Transport()
    {
        return Swift_MailTransport::newInstance();
    }

    function new_Cache_Lite()
    {
        $options = array(
            'cacheDir' => PATH_CACHE,
            'lifeTime' => 3600
        );

        return new Cache_Lite($options);
    }
}
