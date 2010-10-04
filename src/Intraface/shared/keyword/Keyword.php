<?php
/**
 * N�gleord
 *
 * @todo Gruppere n�gleord
 * @todo Systemn�gleord
 *
 * @author Lars Olesen <lars@legestue.net>
 */
require_once 'Intraface/functions.php';
require_once 'Ilib/Keyword.php';

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
                case 'ilib_filehandler':
                    $this->type = 'file_handler';
                    $this->object = $object;
                    break;
                default:
                    throw new Exception('Invalid object - got ' . get_class($this->object));
                    break;
            }

            $this->kernel = $this->object->kernel;
        }
        $extra_conditions = array('intranet_id' => $this->kernel->intranet->get('id'));

        parent::__construct($this->type, $extra_conditions, $id);

        //$object_id = $this->object->get('id');

    }

    /**
     * Skal factory bare tage en kernel og en id og s� selv lave objektet,
     * eller skal det v�re omvendt at factory bruges til at smide et objekt ind i
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
            $this->error->set("Du har ikke skrevet et n�gleord");
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
     * Denne metode sletter et n�gleord i n�gleordsdatabasen
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
                case 'ilib_filehandler':
                    $this->type = 'file_handler';
                    $this->object = $object;
                    break;
                default:
                    throw new Exception(get_class($this) . ' got invalid object ' . get_class($object));
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
     * Denne funktion tilf�jer et n�gleord til et objekt
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
     * Returnerer de keywords der bliver brugt p� nogle poster
     * Is�r anvendelig til s�geoversigter
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
     * Returnerer de keywords, der er tilf�jet til et objekt
     *
     * Det er meget m�rkeligt, men den her funktion returnerer alle keywords p� et intranet?
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
     * Returnerer de vedh�ftede keywords som en streng
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
                    $res = $this->appender->addKeyword($keyword);
                }
            }
        }
        return true;
    }

    /****************************************************************************
     * Tools
     ***************************************************************************/

    /**
     * Funktionen er en hj�lpefunktion, s� man bare kan skrive n�gleordene i et inputfelt
     *
     * @param string $s        The string to split
     * @param string $splitter What splitter to use to split the string
     *
     * @return array med n�gleordene
     */
    public static function quotesplit($s, $splitter=',')
    {
        //First step is to split it up into the bits that are surrounded by quotes and the bits that aren't. Adding the delimiter to the ends simplifies the logic further down
        $getstrings = explode('\"', $splitter.$s.$splitter);
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
                $temparray = explode($splitter, substr($val, $delimlen, strlen($val)-$delimlen-$delimlen ) );

                while (list($iarg, $ival) = each($temparray)) {
                    if (!empty($ival)) $result[] = trim($ival);
                }
                $instring = 1;
            }
        }
        return $result;
    }

}
?>