<?php
/**
 * Extend to the Ilib_DBQuery class to customize it to Intraface
 *
 * @author Sune Jensen <sj@sunet.dk>
 */

require_once 'DB/Sql.php';
require_once 'Ilib/DBQuery.php';

class DBQuery extends Ilib_DBQuery {

    /**
     *
     */
    public function __construct($kernel, $table, $required_conditions = "") {

        parent::__construct($table, $required_conditions);
        $this->createStore($kernel->getSessionId(), 'intranet_id = '.$kernel->intranet->get('id'));
        if(strtolower(get_class($kernel->user)) == 'user') {
            $this->setRowsPerPage($kernel->setting->get('user', 'rows_pr_page'));
        }
    }
}

// should be deleted when Intraface 1.7 is running on server!
class _old_DBQuery {

    /**
     * @var object
     */
    protected $kernel;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $required_condition;

    /**
     * @var object
     */
    protected $error;

    /**
     * @var string
     */
    protected $join;

    /**
     * @var boolean
     */
    protected $use_stored = false;

    /**
     * @var array
     */
    protected $condition = array();

    /**
     * @var array
     */
    protected $sorting = array();

    /**
     * @var array
     */
    protected $filter = array();

    /**
     * @var string
     */
    protected $character;

    /**
     * @var string
     */
    protected $character_var_name;

    /**
     * @var string
     */
    protected $find_character_from_field;

    /**
     * @var boolean
     */
    protected $use_character;

    /**
     * @var string
     */
    protected $extra_uri;

    /**
     * @var array
     */
    protected $keyword_ids = array();

    /**
     * @var sring
     */
    protected $paging_var_name;

    /**
     * @var integer
     */
    protected $rows_pr_page;

    /**
     * @var integer
     */
    protected $recordset_num_rows;

    /**
     * @var integer
     */
    protected $paging_start = NULL;

    /**
     * @var string
     */
    protected $store_var_name;

    /**
     * @var string
     */
    protected $store_name;

    /**
     * @var string
     */
    protected $store_toplevel;

    /**
     * @var string
     */
    protected $store_user_condition;

    /**
     * Constructor
     *
     * @param object $kernel              Kernel @todo remove dependency
     * @param string $table               The table
     * @param string $required_conditions Common conditions
     *
     * @return void
     */
    public function __construct($kernel, $table, $required_conditions = "")
    {
        if (!is_object($kernel)) {
            trigger_error('DBQuery needs kernel', E_USER_ERROR);
            return false;
        }
        $this->kernel = $kernel;
        $this->table = $table;
        $this->required_conditions = $required_conditions;

        $this->recordset_num_rows = 0;
        $this->error = new Error;
        $this->use_character = false;

        if(strtolower(get_class($this->kernel->user)) == 'user') {
            $this->rows_pr_page = $this->kernel->setting->get('user', 'rows_pr_page');
        } else {
            $this->rows_pr_page = 20; // Systemdefault!
        }
        if (is_object($this->kernel->user)) {
            $this->store_user_condition = "user_id = ".$this->kernel->user->get("id");
        } elseif(is_object($this->kernel->weblogin)) {
            $this->store_user_condition = "weblogin_session_id = \"".$this->kernel->weblogin->get("session_id")."\"";
        } else {
            trigger_error('Mangler weblogin eller user', E_USER_ERROR);
        }

        // print("weblogin_session_id = \"".$this->kernel->weblogin->get("session_id")."\"");
    }

    /**
     * Denne funktion benyttes til at definere tabeller, som den skal joines med
     *
     * <code>
     * $dbquery->setJoin('INNER', 'user', 'contact.created_by_user_id=user.id', 'active=1');
     * </code>
     * @param string $type               Either 'INNER', 'LEFT' or 'RIGHT'
     * @param string $table              The table to join
     * @param string $join_on            The fields you want to join in
     * @param string $required_condition Required condititions for the table
     *
     * @return void
     */
    public function setJoin($type, $table, $join_on, $required_condition)
    {
        $i = count($this->join);

        $this->join[$i]["type"] = $type;
        $this->join[$i]["table"] = $table;
        $this->join[$i]["join_on"] = $join_on;
        $this->join[$i]["required_condition"] = $required_condition;
    }

    /**
     * Returns array with join tables and condiditions
     *
     * @return array
     */
    private function getJoin()
    {
        $join["table"] = "";
        $join["condition"] = "";


        for($i = 0, $max = count($this->join); $i < $max; $i++) {
            $join["table"] .= " ".strtoupper($this->join[$i]["type"])." JOIN ".$this->join[$i]["table"]." ON ".$this->join[$i]["join_on"];

            if($this->join[$i]["required_condition"] != "") {
                $join["condition"] .= " AND (".$this->join[$i]["required_condition"].")";
            }
        }

        return $join;
    }

    /**
     * Set extra uri for the getCharacters method
     *
     * @param string $extra_uri Used for the outputter
     *
     * @return void
     */
    public function setExtraUri($extra_uri)
    {
        $this->extra_uri = $extra_uri;
    }

    /**
     * Returnere et array med bogstaver til alfabetisering. Hvis $view = "HTML" returnere den array med HTML link
     *
     * @param string $view Makes it possible to view as HTML
     *
     * @return mixed
     */
    public function getCharacters($view = "")
    {
        // Denne funktion kan optimeres med, at hvis den kaldes 2 gange, så benytter den bare det gamle resultat igen.

        $chars = array();
        if($this->character_var_name != "") {


            $i = 0;
            $tmp = clone $this;

            $tmp->clearAll();
            $tmp->setSorting("bogstav");
            $db = $tmp->getRecordset("distinct(LEFT(".$this->find_character_from_field.", 1)) AS bogstav", "full");

            // Hvis der ikke er mere end et bogstav, så er der ingen grund til character og vi returnere intet
            if($db->numRows() <= 1) {
                return array();
            }

            while ($db->nextRecord()) {


                $bogstav = $db->f('bogstav');

                if (empty($bogstav)) {
                    continue;
                }

                // Hvis det er et mellemrum tager vi den ud
                if(trim($bogstav) == "") {
                    CONTINUE;
                }

                if($view == 'html') {
                    $bogstav = $db->f('bogstav');

                    if($this->character == strtolower($bogstav)) {
                        $chars[$i] = '<strong>'.strtolower($bogstav).'</strong>';
                    }
                    else {
                        $chars[$i] = "<a href=\"".basename($_SERVER["PHP_SELF"])."?".$this->character_var_name."=".strtolower($bogstav)."&amp;".$this->extra_uri."\">".strtolower($bogstav)."</a>";
                    }
                }
                else {
                    $chars[$i] = strtolower($bogstav);
                }
                $i++;
            }
        }

        return $chars;
    }

    /**
     * Returnere et array med tal til paging. Hvis $view = "HTML" returneres et array med links
     *
     * @param string $view Makes it possible to view as HTML
     *
     * @return mixed
     */
    public function getPaging($view = "")
    {
        $but = array();
        $j = 1;

        if($this->store_name != "") {
            $url = "&amp;".$this->store_var_name."=true";
        } elseif($this->character_var_name != "" && isset($_GET[$this->character_var_name])) {
            $url = "&amp;".$this->character_var_name."=".$_GET[$this->character_var_name];
        } else {
            $url = "";
        }

        // print($this->recordset_num_rows.' <= '.$this->rows_pr_page);

        if($this->recordset_num_rows <= $this->rows_pr_page) {
            // Der er færre poster end pr. side. Paging kan ikke betale sig
            return array();
        }

        for($i = 0; $i * $this->rows_pr_page < $this->recordset_num_rows; $i++) {

            if($view == "html") {
                if($this->paging_start == $i*$this->rows_pr_page) {
                    $but[$j] = "<strong>".$j."</strong>";
                } else {
                    $but[$j] = "<a href=\"".basename($_SERVER["PHP_SELF"])."?".$this->paging_var_name."=".($i*$this->rows_pr_page).$url."&amp;".$this->extra_uri."\">".$j."</a>";
                }
            } else {
                $but['offset'][$j] = $i * $this->rows_pr_page;
            }
            $j++;
        }
        /*
        if(!isset($_GET[$this->paging_var_name])) {
            $_GET[$this->paging_var_name] = 0;
        }
        settype($_GET[$this->paging_var_name], "integer");
        */
        if(count($but) > 0) {
            if($this->paging_start > 0) { // $_GET[$this->paging_var_name]
                if($view == "html") {
                    $but[0] = "<a href=\"".basename($_SERVER["PHP_SELF"])."?".$this->paging_var_name."=".($this->paging_start - $this->rows_pr_page).$url."&amp;".$this->extra_uri."\">Forrige</a>"; // $_GET[$this->paging_var_name]
                } else {
                    $but['next'] = $this->paging_start - $this->rows_pr_page; // $_GET[$this->paging_var_name]
                }
            } else {
                $but['previous'] = 0;
            }

            if($this->paging_start < $this->recordset_num_rows - $this->rows_pr_page) { // $_GET[$this->paging_var_name]
                if($view == "html") {
                    $but[$j] = "<a href=\"".basename($_SERVER["PHP_SELF"])."?".$this->paging_var_name."=".($this->paging_start + $this->rows_pr_page).$url."&amp;".$this->extra_uri."\">Næste</a>"; // $_GET[$this->paging_var_name]
                } else {
                    $but['next'] = $this->paging_start + $this->rows_pr_page; // $_GET[$this->paging_var_name]
                }
            }
        }

        return $but;
    }


    /**
     * Returnere størrelsen på recordsettet, samt hvorfra og hvormange
     *
     * @return integer
     */
    protected function getRecordsetSize()
    {
        if(!isset($_GET[$this->paging_var_name])) {
            $show_from = 0;
        } else {
            $show_from = intval($_GET[$this->paging_var_name]);
        }

        $show_to = $show_from + $this->rows_pr_page;
        if($show_to > $this->recordset_num_rows) {
            $show_to = $this->recordset_num_rows;
        }
        $show_from = $show_from + 1;


        return array('number_of_rows' => $this->recordset_num_rows, 'rows_pr_page' => $this->rows_pr_page, 'show_from' => $show_from, 'show_to' => $show_to);
    }

    /************************** FILTER FUNKTIONER *******************************/

    /**
     * Sætter en filter parameter
     * Kan f.eks. være key: "seacrh";  value: $_POST["search"]
     * For at filteret kan bruges til noget, skal det kombineres med setCondition inde i getList funktionen
     *
     * @param string $key   the identifier for the filter
     * @param string $value the value of the filter
     *
     * @return void
     */
    public function setFilter($key, $value)
    {
        $this->filter[$key] = $value;
    }

    /**
     * Checker om et filter er sat
     *
     * @param string $key the identifier on the filter
     *
     * @return boolean
     */
    public function checkFilter($key)
    {
        if(isset($this->filter[$key])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returnere værdien af filteret
     *
     * @param string $key the identifier on the filter
     *
     * @return string
     */
    public function getFilter($key)
    {
        if(isset($this->filter[$key])) {
            return $this->filter[$key];
        } else {
            return "";
        }
    }

    /*************************** FUNKTIONER TIL AT DEFINGERE SØGNINGEN ***********************/

    /**
     * Bruges til at sætte where felterne
     * Flere setConditions kan kaldes, og så vil hver sql-sætning sættes sammen med et AND
     *
     * @param string $string the condidition string to set. Eg. "date > '12-12-2004' OR paid = 0"
     *
     * @return void
     */
    public function setCondition($string)
    {
        $this->condition[] = $string;
    }

    /**
     * Fjerner alle condition, sortings, og keywords
     *
     * @return void
     */
    public function clearAll()
    {
        $this->condition = array();
        $this->sorting = array();
        $this->keyword_ids = array();
    }

    /**
     * Bruges til at sætte order by
     * F.eks. "number, date ASC"
     * Flere sorting kan sættes, og vil blive sat sammen i rækkenfølgen de er sat med et komma.
     *
     * @param string $string set sorting fields
     *
     * @return void
     */
    function setSorting($string)
    {
        $this->sorting[] = $string;
    }

    /**
     * Tjekker om sorting er sat
     *
     * @return boolean
     */
    public function checkSorting()
    {
        return count($this->sorting);
    }

    /**
     * Aktivere alfabetisering. Bruges til at vise poster der starter med character
     *
     * @param string $character_var_name the querystring var name used to set the character
     * @param string $field the field in the database table to the character on.
     *
     * @return void
     */
    public function defineCharacter($character_var_name, $field)
    {
        if($character_var_name != "" && $field != "") {

            $this->character_var_name = $character_var_name;
            $this->find_character_from_field = $field;
        }
    }

    /**
     * Benytter character
     *
     * @return void
     */
    public function useCharacter()
    {
        $this->use_character = true;
    }


    /**
     * Aktivere paging
     *
     * @param string  $paging_var_name the paging querystring variable name. If empty default is used
     * @param integer $rows_pr_page    the number of rows pr page. if not set, default is used.
     *
     * @return void
     */
    public function usePaging($paging_var_name, $rows_pr_page = 0)
    {
        if($paging_var_name != "") {
            $this->paging_var_name = $paging_var_name;
            if((int)$rows_pr_page > 0) {
                $this->rows_pr_page = $rows_pr_page;
            }
        }

        // Hvis den er med i get-strengen, så sætter vi den med det samme.
        if($this->paging_var_name != "" && isset($_GET[$this->paging_var_name])) {
            $this->setPagingOffset($_GET[$this->paging_var_name]);
        }
    }


    /**
     * Til manuelt at sætte paging offset.
     *
     * @param string $offset sets the offset for paging
     *
     * @return void
     */
    public function setPagingOffset($offset)
    {
        $this->paging_start = intval($offset);
    }


    /**
     * Til manuelt at sætte hvormange der skal være pr. side
     *
     * @param integer $number number of rows pr page
     *
     * @return void
     */
    public function setRowsPerPage($number)
    {
        $this->rows_pr_page = (int)$number;
    }

    /**
     * Vælger keywords som kun poster med disse skal vises
     *
     * @param mixed $keyword array with keyword ids or float integer with keyword id on keywords to filter for
     *
     * @return void
     */
    public function setKeyword($keyword)
    {
        if(is_array($keyword)) {
            $this->keyword_ids = $keyword;
        } else {
            $this->keyword_ids = array(intval($keyword));
        }
    }

    /**
     * returns the keyword ids that is set for the filter.
     *
     * @param integer $key key on keywords (i do not know what this is used for)
     *
     * @return mixed
     */
    public function getKeyword($key = -1)
    {
        if((int)$key >= 0) {
            if(isset($this->keyword_ids[$key])) {
                return $this->keyword_ids[$key];
            } else {
                return 0;
            }
        } else {
            return $this->keyword_ids;
        }
    }

    /**************************** ANDRE FUNKTIONER *****************************/

    /**
     * Importer en anden error klasse
     *
     * @param object $error Error object
     *
     * @return void
     */
    public function useErrorObject(&$error)
    {
        $this->error = &$error;
    }

    /**
     * Aktiver gemningen af søgeresultat
     * level: Toplevel benyttes til de primære lister som products, contacts, debtor osv.
     *   Sublevel benyttes når man f.eks. under debtor skal benytte en liste over produkter til at sætte på faktura.
     *   Der vil kun blive stored én toplevel result, mens der vil blive gemt alle sublevel.
     *   Det skyldes at hver gang man har åbnet en toplevel liste, skal skal man ikke se tildigere toplevel søgninger mere.
     *
     * @param string $store_var_name the querystring var name that sets to use stored
     * @param string $store_name     unique store name, that describes the arrea for which the the dbquery is used
     * @param string $level          either 'toplevel' (the store is deleted when other toplevels is set) or 'sublevel' (when sublevel is used it does not effect other stored results)
     *
     * @return void
     */
    public function storeResult($store_var_name, $store_name, $level)
    {
        $this->store_var_name = $store_var_name;
        $this->store_name = $store_name;

        $levels = array(0 => "sublevel", 1 => "toplevel");
        $toplevel = array_search($level, $levels);
        if($toplevel === false) {
            trigger_error("Ugydlig niveau. Skal enten være 'toplevel' eller 'sublevel'", FATAL);
        }

        $this->store_toplevel = $toplevel;

        // Hvis get-variablen er sat, så kan vi lige så godt sætte den med det samme
        if($this->store_name != "" && isset($_GET[$this->store_var_name]) && $_GET[$this->store_var_name] == "true") {
            $this->useStored();
        }
    }

    /**
     * sets that you want to use a stored result
     *
     * @param boolean $value can be set to false to deactivate store.
     *
     * @return void
     */
    public function useStored($value = true)
    {
        if(!in_array($value, array(true, false))) {
            trigger_error("Første parameter til DBQuery->useStored() er ikke ente true eller false", E_USER_ERROR);
        }

        $this->use_stored = $value;
    }

    /*********************** FUNKTIONER TIL AT RETURNERE SQL-STRENG *************************/

    /**
     * Returnere db object med recordset
     *
     * @param string  $fields fields from the tabel you will recive
     * @param string  $use    'full': without any paging, '' with paging
     * @param boolean $print  true will show sql query
     *
     * @return void
     */
    public function getRecordset($fields, $use = "", $print = false)
    {
        $db = new DB_sql;
        $csql = ""; //Definere variable
        $stored_character = false;

        // Henter stored result, hvis det er aktiveret og hvis det bliver efterspurgt.
        // hack use_stored = 1 LO Bruges i webshop. Ved ikke om det er tiltænkt sådan
        if($use != "full" && $this->use_stored)  { // $this->store_name != "" && (isset($_GET[$this->store_var_name]) && $_GET[$this->store_var_name] == "true")

            $db->query("SELECT dbquery_condition, joins, keyword, paging, sorting, filter, first_character FROM dbquery_result WHERE intranet_id = ".$this->kernel->intranet->get("id")." AND ".$this->store_user_condition." AND name = \"".$this->store_name."\"");

            if($db->nextRecord()) {
                $this->condition = unserialize(base64_decode($db->f("dbquery_condition")));
                $this->join = unserialize(base64_decode($db->f("joins")));
                $this->keyword_ids = unserialize(base64_decode($db->f("keyword")));
                if($this->paging_start === NULL) {
                    // Kun hvis den ikke manuelt er blevet sat, så skal den sættes.
                    $this->paging_start = $db->f("paging");
                }
                $this->sorting = unserialize(base64_decode($db->f("sorting")));
                $this->filter = unserialize(base64_decode($db->f("filter")));
                // $this->group_by = unserialize(base64_decode($db->f("group_by")));
                // $this->having_condition = unserialize(base64_decode($db->f("having_condition")));


                $stored_character = $db->f("first_character");
                // Hvis character er sat, så benyttes character
                if($stored_character != "") {
                    $this->use_character = true;
                }
            }
        }

        if($this->paging_start === NULL) {
            // Hvis paging ikke er sat, så skal den bare være 0
            $this->paging_start = 0;
        }

        // Sætter character på
        if($use != "full" && $this->use_character) {

            if($this->character_var_name == "") {
                trigger_error("For at benytte useCharacter(), skal du også benytte defineCharacter()", FATAL);
            }

            if(isset($_GET[$this->character_var_name]) && $_GET[$this->character_var_name] != "") {
                $this->character = $_GET[$this->character_var_name];
            } elseif($stored_character !== false) {
                $this->character = $stored_character;

                // keep it that way
            } else {
                // $tmp_dbquery = clone $this;
                $tmp = $this->getCharacters();
                // Vi tager det første character
                if(array_key_exists(0, $tmp) AND $tmp[0] != "") {
                    $this->character = $tmp[0];
                } else {
                    $this->character = "";
                }
            }

            if($this->character != "") {
                $csql = " AND LEFT(".$this->find_character_from_field.", 1) = \"".$this->character."\"";
            }
        }

        // Henter join sætninger
        $join = $this->getJoin();

        $extra_condition = "";

        // Sætter keyword på joinsætninger
        if($use != "full" && count($this->keyword_ids) != 0) {
            $ksql = "";
            for($i = 0, $max = count($this->keyword_ids); $i < $max; $i++) {
                if($this->keyword_ids[$i] != 0) {
                    if($ksql != '') {
                        $ksql .= " OR";
                    }

                    $ksql .= " keyword_x_object.keyword_id = ".$this->keyword_ids[$i];
                }
            }

            if($ksql != "") {
                $join["table"] .= " LEFT JOIN keyword_x_object ON keyword_x_object.belong_to = ".$this->table.".id";
                $join["condition"] .= " AND (".$ksql.")";
                $extra_condition = "GROUP BY ".$this->table.".id HAVING COUNT(keyword_x_object.keyword_id) = ".count($this->keyword_ids);
            }
        }

        $sql = "FROM ".$this->table."".$join["table"]." WHERE 1 = 1 ";

        if($this->required_conditions != "") {
            $sql .= "AND (".$this->required_conditions.") ";
        }
        $sql .= $join["condition"];

        $sql_end = $this->getSQLString($extra_condition);

        // Tjekker antallet af poster for at se om character er nødvendigt!
        if($csql != "") {
            $db->query("SELECT COUNT(".$this->table.".id) AS num_rows ".$sql.$sql_end);
            $db->nextRecord() OR trigger_error("Kunne ikke eksekvere SQL-sætning", FATAL);

            if($db->f("num_rows") > $this->rows_pr_page) {
                $sql .= $csql; // tilføjer charater
            } else {
                // Så er der ikke nogen grund til at benyttes character
                $this->character_var_name = "";
            }
        }


        $sql .= $sql_end;

        // Laver paging
        if($use != "full" && $this->paging_var_name != "") {

            $db->query("SELECT COUNT(DISTINCT(".$this->table.".id)) AS num_rows ".$sql);
            if($db->nextRecord()) { // Dette er vist lige lovlig dræstisk: OR trigger_error("Kunne ikke eksekvere SQL-sætning", FATAL);
                $this->recordset_num_rows = $db->f("num_rows");
            } else {
                $this->recordset_num_rows = 0;
            }

            if($this->recordset_num_rows > $this->rows_pr_page) {
                $sql .= " LIMIT ".$this->paging_start.", ".$this->rows_pr_page;
            }
        }
        // echo $sql;
        // Gemmer søgeresultatet
        // Skal ikke gemmes når det er et fuldt resultat.
        if($use != "full" && $this->store_name != "") {

            $store_sql = "name = \"".$this->store_name."\",
                dbquery_condition = \"".base64_encode(serialize($this->condition))."\",
                joins = \"".base64_encode(serialize($this->join))."\",
                keyword = \"".base64_encode(serialize($this->keyword_ids))."\",
                paging = ".$this->paging_start.",
                first_character = \"".$this->character."\",
                sorting = \"".base64_encode(serialize($this->sorting))."\",
                filter = \"".base64_encode(serialize($this->filter))."\",
                date_time = NOW()";

                // group_by = \"".base64_encode(serialize($this->group_by))."\",
                // having_condition = \"".base64_encode(serialize($this->having_condition))."\",



            if($this->store_toplevel == 1) {
                $db->query("SELECT id FROM dbquery_result WHERE intranet_id = ".$this->kernel->intranet->get("id")." AND ".$this->store_user_condition." AND toplevel = 1");
                if($db->nextRecord()) {

                    $db->query("UPDATE dbquery_result SET ".$store_sql." WHERE id = ".$db->f("id"));
                } else {

                    $db->query("INSERT INTO dbquery_result SET intranet_id = ".$this->kernel->intranet->get("id").", ".$this->store_user_condition.", toplevel = 1, ".$store_sql);
                }
            } else {
                $db->query("SELECT id FROM dbquery_result WHERE intranet_id = ".$this->kernel->intranet->get("id")." AND ".$this->store_user_condition." AND toplevel = 0 AND name = \"".$this->store_name."\"");
                if($db->nextRecord()) {
                    $db->query("UPDATE dbquery_result SET ".$store_sql." WHERE id = ".$db->f("id"));
                } else {
                    $db->query("INSERT INTO dbquery_result SET intranet_id = ".$this->kernel->intranet->get("id").", ".$this->store_user_condition.", toplevel = 0, ".$store_sql);
                }
            }
        }


        $sql = "SELECT ".$fields." ".$sql;

        if($print) {
            print($sql);
        }

        $db->query($sql);

        return $db;

    }

    /**
     * Retunere streng der bruges i sql-sætning
     *
     * @param string $extra_condition returns the sql string
     *
     * @return string
     */
    protected function getSqlString($extra_condition = "")
    {

        $where = $this->getConditionString();
        $order_by = $this->getSortingString();
        $sql = "";

        if($this->error->isError()) {
            // Hvis der er fejl returnere den default streng
            // Den skal ikke trigger error. Det giver problemer hvis der er fejl i betaling af faktura. Men hvad skal den så? /Sune (29/6 2005)
            //trigger_error("Der er opstået en fejl i dbquery->getSqlString()", ERROR);
        }

        if($where != "") {
            $sql .= " AND ".$where;
        }

        if($extra_condition != '') {
            $sql .= ' '.$extra_condition.' ';
        }

        if($order_by != "") {
            $sql .= " ORDER BY ".$order_by;
        }

        return($sql);
    }


    /**
     * Bruges til at sammensætte condition-strengene
     *
     * @return string
     */
    protected function getConditionString()
    {

        $condition = $this->condition;

        $sql = "";

        for($i = 0, $mi = count($condition); $i < $mi; $i++) {

            if($i != 0) {
                // Alle andre end den første sættes der et AND før.
                $sql .= " AND ";
            }
            $sql .= "(".$condition[$i].")";
        }

        return $sql;
    }

    /**
     * Benyttes til at sammensætte sorting-strengene.
     *
     * @return string
     */
    protected function getSortingString()
    {

        $sorting = $this->sorting;

        $sql = "";

        for($i = 0, $mi = count($sorting); $i < $mi; $i++) {

            if($i != 0) {
                // Alle andre end den første sættes der et , før.
                $sql .= ", ";
            }
            $sql .= $sorting[$i];
        }
        return $sql;
    }

    /**
     * displays either paging or characters
     *
     * @param string $type either 'paging' or 'character'
     *
     * @return string
     */
    public function display($type)
    {
        switch ($type) {
            case 'paging':
                $paging = $this->getPaging('html');
                if(empty($paging)) return '';
                $links = "";
                for($i = 0, $max = count($paging); $i < $max; $i++) {
                    if(array_key_exists($i, $paging) AND $paging[$i] != "") {
                        $links .= $paging[$i]." | ";
                    }
                }

                $size = $this->getRecordsetSize();

                return '<div class="pagingNav">Side: '.$links.'<br />Viser: '.$size['show_from'].' til '.$size['show_to'].' af '.$size['number_of_rows'].'. </div>';
            break;
            case 'character':
                if (count($this->getCharacters("html")) > 0) {
                    $links = implode(" - ", $this->getCharacters("html"));
                    return '<div class="characterNav">- ' . $links . ' -</div>';
                } else {
                    return '';
                }
            break;
        }
    }

    /**
     * get table name
     *
     * @return string
     */
     public function getTableName()
     {
         return $this->table;
     }

     /**
      * returns whether dbquery uses characters
      *
      * @return boolean
      */
     public function getUseCharacter()
     {
         return $this->use_character;
     }

     /**
      * returns the paging var name
      *
      * @return string
      */
     public function getPagingVarName()
     {
         return $this->paging_var_name;
     }
}
?>