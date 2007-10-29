<?php
/**
 * Nøgleord
 *
 * @todo Gruppere nøgleord
 * @todo Systemnøgleord
 *
 * @author Lars Olesen <lars@legestue.net>
 */

require_once 'Intraface/Standard.php';
require_once 'Intraface/Error.php';
require_once 'Intraface/Validator.php';
require_once 'Intraface/functions/functions.php';
require_once 'Intraface/3Party/Database/Db_Sql.php';

abstract class Ilib_Keyword
{
}

class Keyword extends Standard
{

    /**
     * @var array
     */
    public $value;

    /**
     * @var object
     */
    protected $object;

    /**
     * @var object
     */
    public $error;

    /**
     * @var integer
     */
    protected $id;

    /**
     * Skal indeholde tabelnavnet
     *
     * @var array
     */
    protected $types = array(0 => '_invalid_',
                       1 => 'contact',
                       2 => 'product',
                       3 => 'cms_page',
                       4 => 'newfilemanager',
                       5 => 'cms_template');

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
        $this->extra_conditions = array();

        if (get_class($object) == 'FakeKeywordObject') {
            $this->type = 'contact';
            $this->object = $object;
        } else {

            switch (strtolower(get_class($object))) {
                case 'contact':
                    $this->type = 'contact';
                    $this->object = & $object;
                    break;
                case 'product':
                    $this->type = 'product';
                    $this->object = & $object;
                    $this->object->load();
                    break;
                case 'cms_page':
                    $this->type = 'cms_page';
                    $this->object = & $object;
                    break;
                case 'cms_template':
                    $this->type = 'cms_template';
                    $this->object = & $object;
                    break;
                case 'filemanager':
                    $this->type = 'file_handler';
                    $this->object = & $object;
                    break;
                default:
                    trigger_error('Keyword kræver enten Customer, CMSPage, Product eller FileManager som object', E_USER_ERROR);
                    break;
            }
        }

        $this->error = new Error;

        //$object_id = $this->object->get('id');

        $this->id = (int)$id;

        if ($this->id > 0) {
            $this->load();
        }
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

    /**
     * Loader det enkelte keyword
     *
     * @return boolean
     */
    protected function load()
    {
        $db = new DB_Sql;
        $db->query("SELECT id, keyword FROM keyword WHERE keyword.type='".$this->type."' AND intranet_id=".$this->object->kernel->intranet->get('id')." AND id =" . $this->id . " LIMIT 1");
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
        $validator = new Validator($this->error);

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

        $db = new DB_Sql;
        $db->query("SELECT id, active FROM keyword
            WHERE intranet_id = " . $this->object->kernel->intranet->get('id') . "
                AND keyword = '".$var['keyword']."'
                AND type = '".$this->type."'
                AND active = 1");
        if ($db->nextRecord()) {
            return $db->f('id');
        }

        if ($this->id > 0) {
            $sql_type = 'UPDATE ';
            $sql_end = ' WHERE id = ' . $this->id . '
                AND intranet_id = ' . $this->object->kernel->intranet->get('id') . "
                AND type = '" . $this->type ."'";
        } else {
            $sql_type = "INSERT INTO ";
            $sql_end = ", intranet_id = " . $this->object->kernel->intranet->get('id') . ", type = '".$this->type."'";
        }


        $sql = $sql_type . "keyword SET keyword = '".$var['keyword']."'" . $sql_end;
        $db->query($sql);

        if ($this->id == 0) {
            return $db->insertedId();
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
        $db = new DB_Sql;
        $db->query("UPDATE keyword SET active = 0
            WHERE intranet_id = " . $this->object->kernel->intranet->get('id') . "
                AND id = " . $this->id . " AND type = '".$this->type."'");
        return true;
    }

    /**
     * Denne funktion tilføjer et nøgleord til et objekt
     *
     * @param integer $keyword_id
     *
     * @return boolean
     */
    function addKeyword($keyword_id) {
        $keyword_id = (int)$keyword_id;

        $db = new DB_Sql;
        $db->query("SELECT * FROM keyword_x_object
            WHERE intranet_id = " . $this->object->kernel->intranet->get('id') . "
                AND keyword_id = " . $keyword_id . "
                AND belong_to = " . $this->object->get('id'));

        if (!$db->nextRecord()) {
            $db->query("INSERT INTO keyword_x_object
                SET intranet_id = " . $this->object->kernel->intranet->get('id') . ",
                    keyword_id=". $keyword_id . ",
                    belong_to = " . $this->object->get('id'));
        }
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
        $db = new DB_Sql;

        $db->query("SELECT * FROM keyword
            WHERE
                intranet_id = " . $this->object->kernel->intranet->get('id') . "
                    AND keyword.type = '".$this->type."' AND keyword.active = 1 ORDER BY keyword ASC");

        $i = 0;
        while ($db->nextRecord()) {
            $keywords[$i]['id'] = $db->f('id');
            $keywords[$i]['keyword'] = $db->f('keyword');
            $i++;
        }

        return $keywords;
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
        $db = new DB_Sql;

        $db->query("SELECT DISTINCT(keyword.id), keyword.keyword FROM ".$this->type."
            INNER JOIN keyword_x_object x ON ".$this->type.".id=x.belong_to
            INNER JOIN keyword keyword ON x.keyword_id = keyword.id
            WHERE
                keyword.intranet_id = " . $this->object->kernel->intranet->get('id') . "
                    AND keyword.type = '".$this->type."'
                    AND keyword.active = 1
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
        $db = new DB_Sql;

        $db->query("SELECT DISTINCT(keyword.id) AS id, keyword.keyword FROM keyword_x_object
            INNER JOIN keyword
            ON keyword_x_object.keyword_id = keyword.id
            WHERE
                keyword_x_object.belong_to = " . $this->object->get('id') . "
                AND keyword.keyword != ''
                AND keyword.active = 1
                AND keyword.type = '".$this->type."'
                AND keyword.intranet_id = " . $this->object->kernel->intranet->get('id') . "
                AND keyword_x_object.intranet_id =  " . $this->object->kernel->intranet->get('id') . "
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

        $db = new DB_Sql;
        $db->query("DELETE keyword_x_object FROM keyword_x_object INNER JOIN keyword ON keyword_x_object.keyword_id = keyword.id
            WHERE keyword.intranet_id = " . $this->object->kernel->intranet->get('id') . "
                AND keyword_x_object.belong_to = " . $this->object->get('id') . " AND keyword.type = '" . $this->type . "'");

        return true;
    }

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

    /**
     * Add keywords by string
     *
     * @param string $string
     *
     * @return boolean
     */
    function addKeywordsByString($string)
    {
        $this->deleteConnectedKeywords();

        $keywords = $this->quotesplit(stripslashes($string), ",");

        if (is_array($keywords) AND count($keywords) > 0) {
            foreach ($keywords AS $key=>$value) {
                if ($add_keyword_id = $this->save(array('id' => '', 'keyword'=>$value))) {
                    $this->addKeyword($add_keyword_id);
                }
            }
        }
        return true;
    }



    /**
     * Denne funktion henter poster i objektet som hører til et nøgleord
     *
     * @param integer $keyword_id
     *
     * @return array
     */
    function getList($keyword_id)
    {
        $ids = array();
        $sql_keywords = '(';
        $sql_innerjoin = '';
        $sql_keywordtype = '';
        $sql_extrawhere = '';

        if (!empty($keyword_id) AND gettype($keyword_id) == 'array') {
            $i = 0;
            foreach ($keyword_id AS $key=>$value) {
                if ($value > 0 AND $i > 0) {
                    $sql_keywords .= " AND ";
                    $sql_extrawhere .= " AND ";
                    $sql_keywordtype = " AND ";
                }
                if ($value > 0) {
                    $sql_innerjoin .= " INNER JOIN keyword_x_object x$i ON $this->type.id=x$i.belong_to
                        INNER JOIN keyword keyword$i ON x$i.keyword_id = keyword$i.id";
                    $sql_keywords .= " x$i.keyword_id = " . (int)$value;
                    $sql_keywordtype = " keyword$i.type='".$this->type."'";
                    $sql_extrawhere .= "	keyword$i.intranet_id = " . $this->object->kernel->intranet->get('id');

                    $i++;
                }
            }
            $sql_keywords .= ')';
        } elseif (!empty($keyword_id) AND is_numeric($keyword_id)) {
            $sql_innerjoin .= " INNER JOIN keyword_x_object x ON ".$this->type.".id=x.belong_to
                INNER JOIN keyword keyword ON x.keyword_id = keyword.id";
            $sql_keywords = "x.keyword_id = " . (int)$keyword_id;
           $sql_keywordtype = " keyword.type='".$this->type."'";
            $sql_extrawhere .= "	keyword.intranet_id = " . $this->object->kernel->intranet->get('id');
        }

        // INNER JOIN " . $this->type . "_detail detail ON detail." . $this->type . "_id = $this->type.id

        $sql = "SELECT distinct(".$this->type.".id)
                FROM ".$this->type."
                    " . $sql_innerjoin . "
                WHERE " .$sql_keywordtype. "
                    AND " . $sql_keywords . "
                    AND " . $sql_extrawhere . "
                    AND " . $this->type . ".active = 1";

                // ORDER BY detail.name ASC

        $db = new DB_Sql();
        $db->query($sql);

        while ($db->nextRecord()){
            $ids[] = $db->f('id');
        }
        return $ids;
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
    function quotesplit($s, $splitter=',')
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