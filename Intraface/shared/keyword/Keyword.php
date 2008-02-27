<?php
/**
 * Nøgleord
 *
 * @todo Gruppere nøgleord
 * @todo Systemnøgleord
 *
 * @author Lars Olesen <lars@legestue.net>
 */

require_once 'Ilib/Error.php';
require_once 'Ilib/Validator.php';
require_once 'Intraface/functions/functions.php';
require_once 'DB/Sql.php';

abstract class Ilib_Keyword
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $types = array();

    /**
     * @var array
     */
    protected $value = array();

    /**
     * Constructor
     *
     * @return void
     */
    function __construct($type, $extra_conditions = array(), $id = 0)
    {
        $this->id = (int)$id;
        $this->error = new Ilib_Error;
        $this->extra_conditions = $extra_conditions;
        $this->type = $type;
        // @todo before this is changed we need to change all the data in the database
        //$this->type = $this->getTypeKey($this->type);

        if ($this->id > 0) {
            $this->load();
        }
    }

    static function createFromKeyword($type, $extra_conditions = array(), $keyword)
    {
        $condition = $extra_conditions;
        $condition['type'] = $type;
        $condition['keyword'] = $keyword;
        $condition['active'] = 1;

        foreach ($condition as $column => $value) {
            $c[] = $column . " = '" . $value . "'";
        }

        $db = MDB2::singleton(DB_DSN);

        $result = $db->query("SELECT id FROM keyword WHERE "  .  implode(' AND ', $c));

        if (!PEAR::isError($result)) {
            if ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
            return new Ilib_Keyword($type, $extra_conditions, $row['id']);
        }

        throw new Exception('Error in query');
    }

    /**
     * Gets a type for a type key
     *
     * @param integer $key The key for a type
     *
     * @return string
     */
    function getType($key)
    {
        return $this->types[$key];
    }

    /**
     * Gets a type key
     *
     * @param string  $identifier Identifier for a type
     *
     * @return integer
     */
    function getTypeKey($identifier)
    {
        if (!$key = array_search($identifier, $this->types)) {
            throw new Exception('No type registered with this identifier ' . $identifier);
        }
        return $key;
    }

    /**
     * Register a type
     *
     * @param integer $key        The key for a type
     * @param string  $identifier Identifier for a type
     *
     * @return void
     */
    function registerType($key, $identifier)
    {
        $this->types[$key] = $identifier;
    }

    /**
     * Gets the keyword
     *
     * @return string
     */
    function getKeyword()
    {
        return $this->value['keyword'];
    }

    /**
     * Gets the id for a keyword
     *
     * @return integer
     */
    function getId()
    {
        return $this->id;
    }

}

class Keyword extends Ilib_Keyword
{
    /**
     * @var object
     */
    protected $object;

    /**
     * @var object
     */
    public $error;

    /**
     * @var array
     */
    protected $extra_conditions = array();

    /**
     * Constructor
     *
     * @param object  $object
     * @param integer $id
     *
     * @return void
     */
    function __construct($object, $id = 0)
    {
        //@todo type gaar igen som fast parameter
        $this->registerType(0, '_invalid_');
        $this->registerType(1, 'contact');
        $this->registerType(2, 'product');
        $this->registerType(3, 'cms_page');
        $this->registerType(4, 'newfilemanager');
        $this->registerType(5, 'cms_template');

        if (get_class($object) == 'FakeKeywordObject') {
            $this->type = 'contact';
            $this->object = $object;
            $this->kernel = $object->kernel;
        } else {

            switch (strtolower(get_class($object))) {
                case 'contact':
                    $this->type = 'contact';
                    $this->object = $object;
                    break;
                case 'product':
                    $this->type = 'product';
                    $this->object = $object;
                    $this->object->load();
                    break;
                case 'cms_page':
                    $this->type = 'cms_page';
                    $this->object = $object;
                    break;
                case 'cms_template':
                    $this->type = 'cms_template';
                    $this->object = $object;
                    break;
                case 'filemanager':
                    $this->type = 'file_handler';
                    $this->object = $object;
                    break;
                default:
                    trigger_error('Keyword kræver enten Customer, CMSPage, Product eller FileManager som object', E_USER_ERROR);
                    break;
            }

            $this->kernel = $this->object->kernel;
        }
        $extra_conditions = array('intranet_id' => $this->kernel->intranet->get('id'));

        parent::__construct($this->type, $extra_conditions, $id);

        //$object_id = $this->object->get('id');

    }

    /**
     * Skal factory bare tage en kernel og en id og så selv lave objektet,
     * eller skal det være omvendt at factory bruges til at smide et objekt ind i
     * klassen - og at Keyword selv laver objektet?
     *
     * @param object  $kernel
     * @param integer $id
     *
     * @return object
     */
    /*
    public function factory($kernel, $id)
    {
        $id = (int)$id;

        $db = new DB_Sql;
        $db->query("SELECT id, type FROM keyword WHERE id = " . $id . " AND intranet_id=" . $kernel->intranet->get('id'));
        if (!$db->nextRecord()) {
            return 0;
        }

        $class = $db->f('type');

        if (strtolower(get_class($kernel)) == 'fakekeywordkernel') {
            return new Keyword(new FakeKeywordObject(), $db->f('id'));
        }
        $kernel->useModule($class);
        return new Keyword(new $class($kernel), $db->f('id'));
    }
    */

    /**
     * Loader det enkelte keyword
     *
     * @return boolean
     */
    protected function load()
    {
        $condition = $this->extra_conditions;
        $condition['id'] = $this->id;
        $condition['keyword.type'] = $this->type;

        foreach ($condition as $column => $value) {
            $c[] = $column . " = '" . $value . "'";
        }

        $db = new DB_Sql;
        $db->query("SELECT id, keyword FROM keyword
            WHERE " . implode(' AND ', $c));

        if (!$db->nextRecord()) {
            return false;
        }
        $this->value['id'] = $db->f('id');
        $this->value['keyword'] = $db->f('keyword');
        //$this->value['type'] = $db->f('type');
        return true;
    }

    /**
     * Validerer
     *
     * @param array $var
     *
     * @return boolean
     */
    protected function validate($var)
    {
        $validator = new Ilib_Validator($this->error);

        if (!empty($var['id'])) {
            $validator->isNumeric($var['id'], 'id', 'allow_empty');
        }
        if (empty($var['keyword'])) {
            $this->error->set("Du har ikke skrevet et nøgleord");
        }

        if ($this->error->isError()) {
            return false;
        }
        return true;
    }

    /**
     * Gemmer et keyword
     *
     * @param array $var
     *
     * @return integer
     */
    public function save($var)
    {
        settype($var['keyword'], 'string');

        $var['keyword'] = str_replace('"', '', $var['keyword']);
        $var = safeToDb($var);
        $var = array_map('strip_tags', $var);

        if (!$this->validate($var)) {
            return false;
        }
        $c = array();
        $condition = $this->extra_conditions;
        $condition['type'] = $this->type;
        $condition['keyword'] = $var['keyword'];
        $condition['active'] = 1;

        foreach ($condition as $column => $value) {
            $c[] = $column . " = '" . $value . "'";
        }

        $db = new DB_Sql;
        $db->query("SELECT id, active FROM keyword
            WHERE " . implode(' AND ', $c));

        if ($db->nextRecord()) {
            $this->id = $db->f('id');
            $this->load();
            return $this->id;
        }

        if ($this->id > 0) {
            $c = array();
            $condition = array();
            $condition = $this->extra_conditions;
            $condition['id'] = $this->id;
            $condition['type'] = $this->type;

            foreach ($condition as $column => $value) {
                $c[] = $column . " = '" . $value . "'";
            }

            $sql_type = 'UPDATE ';
            $sql_end = ' WHERE ' . implode(' AND ', $c);

        } else {
            $c = array();
            $condition = array();
            $condition = $this->extra_conditions;
            $condition['type'] = $this->type;

            foreach ($condition as $column => $value) {
                $c[] = $column . " = '" . $value . "'";
            }

            $sql_type = "INSERT INTO ";
            $sql_end = ", " . implode(', ', $c);
        }

        $sql = $sql_type . "keyword SET keyword = '".$var['keyword']."'" . $sql_end;
        $db->query($sql);

        if ($this->id == 0) {
            $this->id = $db->insertedId();
        }
        $this->load();
        return $this->id;
    }

    /**
     * Denne metode sletter et nøgleord i nøgleordsdatabasen
     *
     * @return boolean
     */
    function delete()
    {
        if ($this->id == 0) {
            return false;
        }

        $condition = $this->extra_conditions;
        $condition['id'] = $this->id;
        $condition['type'] = $this->type;
        foreach ($condition as $column => $value) {
            $c[] = $column . " = '" . $value . "'";
        }

        $db = new DB_Sql;
        $db->query("UPDATE keyword SET active = 0
            WHERE " . implode(' AND ', $c));

        return true;
    }

    /**
     * Egentlig en slags getList i keywords
     *
     * @return array
     */
    function getAllKeywords()
    {
        $keywords = array();

        $condition = $this->extra_conditions;
        $condition['keyword.type'] = $this->type;
        $condition['keyword.active'] = 1;
        foreach ($condition as $column => $value) {
            $c[] = $column . " = '" . $value . "'";
        }

        $db = new DB_Sql;
        $db->query("SELECT * FROM keyword
            WHERE " . implode(' AND ', $c) . "
            ORDER BY keyword ASC");

        $i = 0;
        while ($db->nextRecord()) {
            $keywords[$i]['id'] = $db->f('id');
            $keywords[$i]['keyword'] = $db->f('keyword');
            $i++;
        }

        return $keywords;
    }
}

class Intraface_Keyword_Appender extends Keyword
{
    protected $object;
    protected $type;
    protected $extra_conditions;
    protected $belong_to_id;
    public $error;

    function __construct($object)
    {
        if (get_class($object) == 'FakeKeywordAppendObject') {
            $this->type = 'contact';
            $this->object = $object;
            $this->kernel = $object->kernel;
        } else {

            switch (strtolower(get_class($object))) {
                case 'contact':
                    $this->type = 'contact';
                    $this->object = $object;
                    break;
                case 'product':
                    $this->type = 'product';
                    $this->object = $object;
                    $this->object->load();
                    break;
                case 'cms_page':
                    $this->type = 'cms_page';
                    $this->object = $object;
                    break;
                case 'cms_template':
                    $this->type = 'cms_template';
                    $this->object = $object;
                    break;
                case 'filemanager':
                    $this->type = 'file_handler';
                    $this->object = $object;
                    break;
                default:
                    trigger_error(get_class($this) . ' kræver enten Customer, CMSPage, Product eller FileManager som object', E_USER_ERROR);
                    break;
            }

            $this->kernel = $this->object->kernel;

            $this->belong_to_id = $this->object->getId();
            $this->error = new Ilib_Error;

        }
        $this->extra_conditions = array('intranet_id' => $this->object->kernel->intranet->get('id'));
    }

    function getBelongToId()
    {
        return $this->belong_to_id;
    }

    /**
     * Denne funktion tilføjer et nøgleord til et objekt
     *
     * @param integer $keyword_id
     *
     * @return boolean
     */
    function addKeyword($keyword) {
        $condition = $this->extra_conditions;
        $condition['keyword_x_object.keyword_id'] = $keyword->getId();
        $condition['keyword_x_object.belong_to'] = $this->getBelongToId();

        foreach ($condition as $column => $value) {
            $c[] = $column . " = '" . $value . "'";
        }

        $db = new DB_Sql;
        $db->query("SELECT * FROM keyword_x_object
            WHERE " . implode(' AND ', $c));

        if (!$db->nextRecord()) {
            $db->query("INSERT INTO keyword_x_object
                SET " . implode(', ', $c));

        }
        return true;
    }

    /**
     * Add keywords from an array
     *
     * @param array $keywords
     *
     * @return boolean
     */
    function addKeywords($keywords)
    {
        if (is_array($keywords) AND count($keywords) > 0) {
            foreach ($keywords AS $keyword) {
                $this->addKeyword($keyword);
            }
        }
        return true;
    }

    /**
     * Returnerer de keywords der bliver brugt på nogle poster
     * Især anvendelig til søgeoversigter
     *
     * @return array
     */
    function getUsedKeywords()
    {
        $keywords = array();

        //$condition = $this->extra_conditions;
        $condition['keyword.intranet_id'] = $this->kernel->intranet->get('id');
        $condition['keyword.type'] = $this->type;
        $condition['keyword.active'] = 1;

        foreach ($condition as $column => $value) {
            $c[] = $column . " = '" . $value . "'";
        }

        $db = new DB_Sql;
        $db->query("SELECT DISTINCT(keyword.id), keyword.keyword
            FROM keyword_x_object x
            INNER JOIN keyword keyword
                ON x.keyword_id = keyword.id
            WHERE " . implode(' AND ', $c) . "
            ORDER BY keyword ASC");

        $i = 0;
        while ($db->nextRecord()) {
            $keywords[$i]['id'] = $db->f('id');
            $keywords[$i]['keyword'] = $db->f('keyword');
            $i++;
        }

        return $keywords;
    }

    /**
     * Returnerer de keywords, der er tilføjet til et objekt
     *
     * Det er meget mærkeligt, men den her funktion returnerer alle keywords på et intranet?
     *
     * @return array
     */
    function getConnectedKeywords()
    {
        $keywords = array();

        //$condition = $this->extra_conditions;
        $condition['keyword.active '] = 1;
        $condition['keyword.type '] = $this->type;
        $condition['keyword.intranet_id '] = $this->kernel->intranet->get('id');

        $condition['keyword_x_object.intranet_id '] = $this->kernel->intranet->get('id');
        $condition['keyword_x_object.belong_to'] = $this->getBelongToId();

        foreach ($condition as $column => $value) {
            $c[] = $column . " = '" . $value . "'";
        }

        $db = new DB_Sql;
        $db->query("SELECT DISTINCT(keyword.id) AS id, keyword.keyword
            FROM keyword_x_object
            INNER JOIN keyword
            ON keyword_x_object.keyword_id = keyword.id
            WHERE " . implode(' AND ', $c) . " AND keyword.keyword != ''
            ORDER BY keyword.keyword");

        $i = 0;
        while ($db->nextRecord()) {
            $keywords[$i]['id'] = $db->f('id');
            $keywords[$i]['keyword'] = $db->f('keyword');
            $i++;
        }

        return $keywords;
    }

    /**
     * Delete all connected keywords to an object
     *
     * @return boolean
     */
    function deleteConnectedKeywords()
    {
        if ($this->object->get('id') == 0) {
            return false;
        }

        //$condition = $this->extra_conditions;
        $condition['keyword.intranet_id'] = $this->kernel->intranet->get('id');
        $condition['keyword.type '] = $this->type;

        $condition['keyword_x_object.belong_to'] = $this->getBelongToId();

        foreach ($condition as $column => $value) {
            $c[] = $column . " = '" . $value . "'";
        }


        $db = new DB_Sql;
        $db->query("DELETE keyword_x_object FROM keyword_x_object
            INNER JOIN keyword ON keyword_x_object.keyword_id = keyword.id
            WHERE " . implode(' AND ', $c));

        return true;
    }

    ///////////////////////////////////////////////////////////////////////////
    // INGEN DB I DE FOLGENDE
    ///////////////////////////////////////////////////////////////////////////

    /**
     * Returnerer de vedhæftede keywords som en streng
     *
     * @return string
     */
    function getConnectedKeywordsAsString()
    {
        $keywords = $this->getConnectedKeywords();
        $arr = array();

        foreach ($keywords AS $keyword) {
            $arr[] = $keyword['keyword'];
        }
        $string = implode(', ', $arr);

        return trim($string);
    }
}

class Intraface_Keyword_StringAppender
{
    private $keyword_class;
    private $appender;

    function __construct($keyword, $appender)
    {
        $this->keyword_class = $keyword;
        $this->appender = $appender;
    }

    private function cloneKeyword()
    {
        return clone $this->keyword_class;
    }

    /**
     * Add keywords by string
     *
     * @param string $string
     *
     * @return boolean
     */
    public function addKeywordsByString($string)
    {
        $this->appender->deleteConnectedKeywords();

        $keywords = self::quotesplit(stripslashes($string), ",");

        if (is_array($keywords) AND count($keywords) > 0) {
            foreach ($keywords AS $key => $value) {
                $keyword = $this->cloneKeyword();
                if ($keyword->save(array('keyword' => $value))) {
                    $this->appender->addKeyword($keyword);
                }
            }
        }
        return true;
    }

    /****************************************************************************
     * Tools
     ***************************************************************************/

    /**
     * Funktionen er en hjælpefunktion, så man bare kan skrive nøgleordene i et inputfelt
     *
     * @param string $s        The string to split
     * @param string $splitter What splitter to use to split the string
     *
     * @return array med nøgleordene
     */
    public static function quotesplit($s, $splitter=',')
    {
        //First step is to split it up into the bits that are surrounded by quotes and the bits that aren't. Adding the delimiter to the ends simplifies the logic further down
        $getstrings = split('\"', $splitter.$s.$splitter);
        //$instring toggles so we know if we are in a quoted string or not
        $delimlen = strlen($splitter);
        $instring = 0;
        $result = array();

        while (list($arg, $val) = each($getstrings)) {
            if ($instring==1) {
                //Add the whole string, untouched to the result array.
                if (!empty($val)) {
                    $result[] = $val;
                    $instring = 0;
                }
            } else {
                //Break up the string according to the delimiter character
                //Each string has extraneous delimiters around it (inc the ones we added above), so they need to be stripped off
                $temparray = split($splitter, substr($val, $delimlen, strlen($val)-$delimlen-$delimlen ) );

                while(list($iarg, $ival) = each($temparray)) {
                    if (!empty($ival)) $result[] = trim($ival);
                }
                $instring = 1;
            }
        }
        return $result;
    }

}
?>