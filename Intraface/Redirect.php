<?php
/**
 * Redirects a user to specific pages
 *
 * @package Intraface
 * @author  Sune Jensen <sj@sunet.dk>
 * @version @package-version@
 */

require_once 'Intraface/3Party/Database/Db_sql.php';

/**
 * Redirects a user to specific pages
 *
 * Usage:
 *
 * On the page where the user starts to get into the redirect cycle (not necessary the
 * page the user returns to afterwards):
 *
 * <code>
 * // optional - variable sent in the url with id on redirect. Must be the same on sender and receiver pages.
 * $other_querystring_name        = '';
 * // optional
 * $other_return_querystring_name = '';
 *
 * $redirect = Redirect::go($kernel, $other_querystring_name, $other_return_querystring_name);
 *
 * $return_url      = 'http://http://example.dk/state.php/state.php?id=1';
 * $destination_url = 'http://example.dk/page.php';
 * $url = $redirect->setDestination($destination_url, $return_url);
 *
 * $parameter_to_return_with = 'add_contact_id'; // activates the parameter sent back to the return page
 * $how_many_parameters = ''; // could also be multiple if more parameters should be returned
 *
 * // optional method calls
 * $redirect->askParameter($parameter_to_return_with, [, 'multiple']);
 * // Identifier kan be set, if you have more redirects on the same page
 * // Makes it possible to return to the right redirect.
 * $redirect->setIdentifier('sted_1');
 *
 * // Doing the redirect
 * header('Location: '' . $url);
 * exit;
 * </code>
 *
 * On the page the user is sent to - and is later sent back to the previous page.
 *
 * <code>
 * // optional - variable sent in the url with id on redirect. Must be the same on sender and receiver pages.
 * $other_querystring_name        = '';
 * // optional
 * $other_return_querystring_name = '';
 *
 * // Must be called on every page show
 * $redirect = Redirect::receive($kernel, $other_querystring_name, $other_return_querystring_name = '';);
 *
 * if(isset($_POST['submit'])) {
 *     // save something
 *     // optional parameter
 *     $redirect->setParameter("add_contact_id", $added_id); // Denne sætter parameter som skal sendes tilbage til siden. Den sendes dog kun tilbage hvis askParameter er sat ved opstart af redirect. Hvis ask er sat til multiple, sï¿½ gemmes der en ny hver gang den aktiveres, hvis ikke, overskrives den
 *
 *     // the redirect
 *     $standard_page_without_redirect = 'standard.php';
 *     header('Location: '.$redirect->getRedirect($standard_page_without_redirect));
 *     exit;
 * }
 *
 * <a href="<?php echo $redirect->getRedirect('standard.php'); ?>">Cancel</a>
 * </code>
 *
 * If you need to make a redirect which spans more redirects, like going from:
 *
 * first.php --> second.php --> third.php
 *
 * You can do the following (@todo ON WHICH PAGE?):
 *
 * <code>
 * if($go_further) {
 * 	   $new_redireict = Redirect::go($kernel);
 * 	   $url = $new_redirect->setDestination('http://example.dk/first.php', 'http://example.dk/second.php?' . $redirect->get('redirect_query_string'));
 * 	   header('Location: ' . $url);
 *     exit;
 * }
 * </code>
 *
 * Notice that redirect_query_string has redirect_id=<id> on the page where redirect is set
 * (@todo WHICH PAGE IS THAT?).
 *
 * The final page of the redirect cycle (often the same page you started from) you can retrieve
 * the parameter again:
 *
 * <code>
 * if(isset($_GET['return_redirect_id'])) {
 *     $redirect = Redirect::return($kernel);
 *     // optional
 *     $redirect->getIdentifier(); returns the identifier set in the beginning
 *
 *     // retrieves the value - returns array if ask was 'multiple' else just the value
 *     $selected_values = $redirect->getParameter('add_contact_id');
 *
 *     // deletes the redirect, so that the action is not done again on the
 *     // use of Back button (@todo IS THIS OPTIONAL OR NECCESSARY)
 *     $redirect->delete();
 * }
 * </code>
 *
 * Notice:
 *
 * The system to automatically get redirect_id and return_redirect_id is based on $_GET variables.
 * If there is a need for $_POST write Sune Jensen <sj@sunet.dk>.
 *
 * For the time being it is possible to use:
 *
 * <code>
 * $redirect = new Redirect($kernel, $_POST['redirect_id|return_redirect_id']);
 * $redirect->reset();
 * </code>
 *
 * @package Intraface
 * @author  Sune Jensen <sj@sunet.dk>
 * @version @package-version@
 */

class Redirect
{
    /**
     * @var object
     */
    private $kernel;

    /**
     * @var array
     */
    public $value;

    /**
     * @var array
     */
    private $querystring = array();

    /**
     * @var string
     */
    private $identifier;

    /**
     * Constructs a redirect object
     *
     * @param object  $kernel @todo THIS SHOULD BE SUBSTITUTED WITH SESSION_ID
     * @param integer $id     Id of the redirect
     *
     * @return object
     */
    function __construct($kernel, $id = 0)
    {
        $this->kernel = $kernel;

        $this->value['query_variable'] = 'redirect_id';
        $this->value['query_return_variable'] = 'return_redirect_id';
        $this->value['id'] = 0;

        $this->id = (int)$id;
        if($this->id > 0) {
            $this->load();
        }
    }

    /**
     * Creates a redirect object on the go page
     *
     * @param object $kernel                @todo THIS SHOULD BE SUBSTITUTED WITH SESSION_ID
     * @param string $query_variable        @todo is this used for go?
     * @param string $query_return_variable @todo is this used for go?
     *
     * @return object
     */
    function go($kernel, $query_variable = 'redirect_id', $query_return_variable = 'return_redirect_id')
    {
        return self::factory($kernel, 'go', $query_variable, $query_return_variable);
    }

    /**
     * Creates a redirect object on the receiving page
     *
     * @param object $kernel                @todo THIS SHOULD BE SUBSTITUTED WITH SESSION_ID
     * @param string $query_variable        EXPLAIN
     * @param string $query_return_variable EXPLAIN
     *
     * @return object
     */
    function receive($kernel, $query_variable = 'redirect_id', $query_return_variable = 'return_redirect_id')
    {
        return self::factory($kernel, 'receive', $query_variable, $query_return_variable);
    }

    /**
     * Creates a redirect object on the returning page
     *
     * @param object $kernel                @todo THIS SHOULD BE SUBSTITUTED WITH SESSION_ID
     * @param string $query_variable        EXPLAIN
     * @param string $query_return_variable EXPLAIN
     *
     * @return object
     */
    function returns($kernel, $query_variable = 'redirect_id', $query_return_variable = 'return_redirect_id')
    {
        return self::factory($kernel, 'return', $query_variable, $query_return_variable);
    }

    /**
     * Creates a redirect object
     *
     * This should be substituted with specific methods for the types
     *
     * @param object $kernel                @todo THIS SHOULD BE SUBSTITUTED WITH SESSION_ID
     * @param string $type                  Can be either go, receive or return
     *                                      WHAT IS THE DIFFERENCES
     * @param string $query_variable        EXPLAIN
     * @param string $query_return_variable EXPLAIN
     *
     * @return object
     */
    function factory($kernel, $type, $query_variable = 'redirect_id', $query_return_variable = 'return_redirect_id')
    {

        if(!is_object($kernel)) {
            trigger_error("First parameter in redirect::factory is not kernel", E_USER_ERROR);
        }

        if(!in_array($type, array('go', 'receive', 'return'))) {
            trigger_error("Anden parameter i Redirect->factory er ikke enten 'go', 'receive' eller 'return'", E_USER_ERROR);
        }

        $reset = false;
        $id = 0;
        if($type == 'go') {
            // Vi starter en ny redirect på siden, derfor skal vi ikke her slette eksisterende redirects til denne side.
            $id = 0;
        } else {
            if(($type == 'receive' && isset($_GET[$query_variable]))) {
                // Vi modtager en redirect fra url'en. Derfor sletter vi alle andre redirects til denne side.
                $reset = true;
                $id = intval($_GET[$query_variable]);

            } elseif($type == 'return' && isset($_GET[$query_return_variable])) {
                // Vi returnerer med en værdi. Der kan være en eksisterende redirect til denne side, som vi skal benytte igen. Vi sletter ikke andre redirects.
                $id = intval($_GET[$query_return_variable]);
            } elseif(isset($_SERVER['HTTP_REFERER'])) {
                // Vi arbejder inden for samme side. Vi finder forhåbentligt en redirect. Under alle omstændigheder sletter vi hvad vi ikke skal bruge.
                $reset = true;

                $url_parts = explode("?", $_SERVER['HTTP_REFERER']);
                // print("b");

                $this_uri = Redirect::thisUri();

                // print($this_uri.' == '.$url_parts[0]);
                if($this_uri == $url_parts[0]) {
                    // print("c");
                    // Vi arbejder inden for den samme side, så finder vi id ud fra siden.
                    $db = new DB_sql;
                    //print "SELECT id FROM redirect WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND user_id = ".$this->kernel->user->get('id')." AND destination_url = \"".$_SERVER["SCRIPT_URI"]."\"";
                    $db->query("SELECT id FROM redirect WHERE intranet_id = ".$kernel->intranet->get('id')." AND user_id = ".$kernel->user->get('id')." AND destination_url = \"".$this_uri."\" ORDER BY date_created DESC");
                    if($db->nextRecord()) {
                        $id = $db->f('id');
                    } else {
                        $id = 0;
                    }
                } else {
                    // print("d");
                    // Der er ikke sat et redirect_id, vi er ikke inden for samme side, så må det være et kald til siden som ikke benytter redirect. Vi sletter alle redirects til denne side.
                    $reset = true;
                    $id = 0;
                }
            }
        }

        $redirect = new Redirect($kernel, $id);
        if($reset) {
            $redirect->reset();
        }
        $redirect->set('query_variable', $query_variable);
        $redirect->set('query_return_variable', $query_return_variable);

        return $redirect;
    }

    /**
     * Sets a key
     *
     * @return void
     */
    function set($key, $value)
    {
        if($key != '') {
            $this->value[$key] = $value;
        } else {
            trigger_error("Key er ikke sat i Redirect->set", E_USER_ERROR);
        }
    }

    /**
     * Loads information about the redirect
     *
     * @return integer
     */
    private function load()
    {

        $db = new DB_Sql;
        $sql = "SELECT * FROM redirect
            WHERE intranet_id = ".$this->kernel->intranet->get('id')."
            AND user_id = ".$this->kernel->user->get('id')."
            AND id = ".$this->id;
        $db->query($sql);
        if(!$db->nextRecord()) {
            $this->id = 0;
            $this->value['id'] = 0;
            return 0;
        }

        $this->value['id'] = $db->f('id');
        $this->value['from_url'] = $db->f('from_url');
        $this->value['return_url'] = $db->f('return_url');
        $this->value['destination_url'] = $db->f('destination_url');
        $this->value['identifier'] = $db->f('identifier');


        $this->value['redirect_query_string'] = $this->get('query_variable')."=".$this->id;

        return $this->id;
    }

    /**
     * Parses an url and makes it save
     *
     * @todo actually add functionality
     *
     * @param string $url Url to parse
     *
     * @return string
     */
    function parseUrl($url)
    {
        return $url;
    }

    /**
     * Sets an identifier
     *
     * @param string $identifier The identifier to use
     *
     * @return string
     */
    function setIdentifier($identifier)
    {
        if($this->id) {
            $db = new DB_sql;
            $db->query("UPDATE redirect SET identifier = \"".safeToDb($identifier)."\" WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".$this->id);
            return true;
        } else {
            $this->identifier = safeToDB($identifier);
            return true;
        }
    }

    /**
     * Returns the uri to current file
     *
     * @return string
     */
    function thisUri()
    {
        $protocol = 'http://';
        if(!empty($_SERVER['HTTPS'])) {
            $protocol= 'https://';
        }
        return $protocol. $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
    }

    /**
     * Set destination
     *
     * @param string $url        Destination url. The url redirect should work from
     * @param string $return_url Url to return to WHAT DOES THAT MEAN - GIVE A CODE EXAMPLE
     *
     * @return string The url which should be used for the redirect
     */
    function setDestination($destination_url, $return_url = '')
    {
        if (!array_key_exists('SCRIPT_URI', $_SERVER)) {
            if (!empty($_SERVER['REQUEST_URI'])) {
                $_SERVER['SCRIPT_URI'] = $_SERVER['REQUEST_URI'];
            } else {
                $_SERVER['SCRIPT_URI'] = '';
            }
            //TODO WHAT TO DO WHEN THE REQUEST URI IS EMPTY
        }

        if(empty($return_url)) {

            $return_url = $this->parseUrl($this->thisUri());
        } else {
            $return_url = $this->parseUrl($return_url);
        }

        $destination_url = $this->parseUrl($destination_url);


        if(substr($destination_url, 0, 7) != 'http://' && substr($destination_url, 0, 8) != 'https://') {
            trigger_error("Fï¿½rste parameter i Redirect->setDestination skal vï¿½re den komplette sti", E_USER_ERROR);
        }

        if(substr($return_url, 0, 7) != 'http://' && substr($return_url, 0, 8) != 'https://') {
            trigger_error("Anden parameter i Redirect->setDestination skal vï¿½re den komplette sti", E_USER_ERROR);
        }


        // Det er kun den rene url der skal gemmes uden query strings, sï¿½ den senere kan sammenlignes med $_SERVER['SCRIPT_URI']
        $url_parts = explode("?", $destination_url);

        $db = new DB_Sql;
        $db->query("INSERT INTO redirect
            SET
                from_url = \"".$_SERVER['SCRIPT_URI']."\",
                return_url = \"".$return_url."\",
                destination_url = \"".$url_parts[0]."\",
                intranet_id = ".$this->kernel->intranet->get('id').",
                user_id = ".$this->kernel->user->get('id').",
                identifier = \"".$this->identifier."\",
                date_created = NOW()");
        $this->id = $db->insertedId();
        $this->load();

        $destination_url = $this->mergeQueryString($destination_url, $this->get('redirect_query_string'));

        return $destination_url;
    }

    /**
     * Only redirect is performed if this url is the same as the url_destination in the
     * database.
     *
     * @param string $standard_location A fall back location if no redirect matches the one asked for
     *
     * @return string
     */
    function getRedirect($standard_location)
    {
        if($this->id > 0) {
            $this->addQuerystring($this->get('query_return_variable').'='.$this->id);
            return $this->mergeQuerystring($this->get('return_url'), $this->querystring);
        } else {
            return $standard_location;
        }
    }

    /**
     * Adds querystring to return_url
     *
     * @param string $querystring Querystring to add to the url
     *
     * @return void
     */
    function addQueryString($querystring)
    {
        // if querystring already set, do not set it again
        if(in_array($querystring, $this->querystring) === false) {
            $this->querystring[] = $querystring;
        }
    }

    /**
     * Merges extra parameters on existing querystring with the right & or ?
     *
     * @param string $querystring
     * @param string $extra       Can be both a string or an array with parameter to add TO WHAT?
     *
     * @return string
     */
    function mergeQueryString($querystring, $extra)
    {

        if(strstr($querystring, "?") === false) {
            $separator = "?";
        } else {
            $separator = '&';
        }

        if(is_array($extra) && count($extra) > 0) {
            return $querystring.$separator.implode('&', $extra);
        } elseif(is_string($extra) && $extra != "") {
            return $querystring.$separator.$extra;
        } else {
            return $querystring;
        }

    }

    /**
     * Resets old redirects
     *
     * @return boolean
     */
    function reset()
    {
        if($this->id == 0) {
            // @todo Kan de nu også være rigtigt at den ikke kan slette hvor id er 0!
            // trigger_error("id er ikke sat i Redirect->reset", E_USER_ERROR);
        }

        $db = new DB_Sql;

        // Vi sletter de
        $db->query("SELECT id FROM redirect
            WHERE
                (intranet_id = ".$this->kernel->intranet->get('id')."
                    AND user_id = ".$this->kernel->user->get('id')."
                    AND id != ".$this->id."
                    AND destination_url = \"".$this->thisUri()."\")
                OR (intranet_id = ".$this->kernel->intranet->get('id')."
                    AND date_created < DATE_SUB(NOW(), INTERVAL 24 HOUR))");

        while($db->nextRecord()) {
            $this->delete($db->f('id'));
        }

        return true;
    }

    /**
     * Delete a single redirect
     *
     * @param integer $id Id of redirect or if not set the current redirect.
     *
     * @return boolean true on success
     */
    function delete($id = NULL)
    {
        if($id === NULL) {
            $id = $this->id;
        }
        if($id == 0) {
            return true;
        }
        $db = new DB_Sql;
        $db->query("DELETE FROM redirect_parameter_value WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND redirect_id = ".intval($id));
        $db->query("DELETE FROM redirect_parameter WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND redirect_id = ".intval($id));
        $db->query("DELETE FROM redirect WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND id = ".intval($id));
        return true;
    }

    /**
     * Used to set a parameter - if more parameters should be set
     *
     * @todo WHY IS THIS METHOD CALLED ASK?
     *
     * @param string $key  Identifier of the parameter
     * @param type   $type Can be either mulitple or single
     *
     * @return boolean
     */
    public function askParameter($key, $type = 'single')
    {
        $key = safeToDb($key);
        $type = safeToDb($type);
        if($this->id == 0) {
            trigger_error("You need to use setDestination() before you use askParameter()", E_USER_EROR);
            return false;
        }

        $multiple = 0;
        if(!in_array($type, array('single', 'multiple'))) trigger_error('Invalid type "'.$type.'" in Redirect->askParameter. It can either be "single" or "multiple"', E_USER_ERROR);
        if($type == 'multiple') $multiple = 1;

        $db = new DB_Sql;
        $db->query("INSERT INTO redirect_parameter SET intranet_id = ".$this->kernel->intranet->get('id').", redirect_id = ".$this->id.", parameter = \"".$key."\", multiple = \"".$multiple."\"");
        return true;
    }

    /**
     * Sets a parameter - both single and multiple - must be called right before location
     *
     * SHOW AN EXAMPLE
     *
     * @return boolean
     */
    public function setParameter($key, $value, $extra_value = '')
    {
        if($this->id == 0) {
            trigger_error("id is not set IN Redirect->setParameter. You might want to consider the possibility that redirect id both could and could not be set by the call of setParameter, and therefor want to make a check before.", E_USER_ERROR);
        }

        $key = safeToDb($key);
        $value = safeToDb($value);
        $extra_value = safeToDb($extra_value);

        $db = new DB_sql;
        $db->query("SELECT id, multiple FROM redirect_parameter WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND redirect_id = ".$this->id." AND parameter = \"".$key."\"");
        if($db->nextRecord()) {
            $parameter_id = $db->f('id');

            if($db->f('multiple') == 1) {
                $db->query("INSERT INTO redirect_parameter_value SET intranet_id = ".$this->kernel->intranet->get('id').", redirect_id = ".$this->id.", redirect_parameter_id = ".$db->f('id').", value = \"".$value."\", extra_value = \"".$extra_value."\"");
                return true;
            }
            else {

                $db->query("SELECT id FROM redirect_parameter_value WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND redirect_id = ".$this->id." AND redirect_parameter_id = ".$db->f('id'));
                if($db->nextRecord()) {
                    $db->query("UPDATE redirect_parameter_value SET value = \"".$value."\", extra_value = \"".$extra_value."\" WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND redirect_id = ".$this->id." AND  redirect_parameter_id = ".$parameter_id);
                } else {
                    $db->query("INSERT INTO redirect_parameter_value SET intranet_id = ".$this->kernel->intranet->get('id').", redirect_id = ".$this->id.", redirect_parameter_id = ".$parameter_id.", value = \"".$value."\", extra_value = \"".$extra_value."\"");
                }
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * Tells whether the request is a multiple value
     *
     * @param string $key The identifer of the parameter
     *
     * @return boolean
     */
    public function isMultipleParameter($key)
    {
        if($this->id == 0) {
            trigger_error("id er ikke sat i Redirect->isMultipleParameter", E_USER_ERROR);
        }
        $key = safeToDb($key);
        $db = new DB_sql;
        $db->query("SELECT id FROM redirect_parameter WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND redirect_id = ".$this->id." AND parameter = \"".$key."\" AND multiple = 1");
        return $db->nextRecord();
    }

    /**
     * Removes a parameter
     *
     * @param string $key   The key of the value to remove
     * @param array  $value The value to remove
     *
     * @return mixed
     */
    public function removeParameter($key, $value)
    {
        if($this->id == 0) {
            trigger_error("id er ikke sat i Redirect->removeParameter", E_USER_ERROR);
        }

        $key = safeToDb($key);
        $value = safeToDb($value);

        $db = new DB_sql;
        $db->query("SELECT id FROM redirect_parameter WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND redirect_id = ".$this->id." AND parameter = \"".$key."\"");
        if($db->nextRecord()) {
            $db->query("DELETE FROM redirect_parameter_value WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND redirect_id = ".$this->id." AND redirect_parameter_id = \"".$db->f('id')."\" AND value = \"".$value."\"");
            return true;
        }
        return false;
    }
    
    /**
     * Gets multiple parameter
     *
     * @param string $key              Gets the following parameter
     * @param array  $with_extra_value @todo WHAT IS THIS
     *
     * @return mixed
     */
    public function getParameter($key, $with_extra_value = '')
    {
        if($this->id == 0) {
            trigger_error('id er ikke sat i Redirect->getMultipleParameter', E_USER_ERROR);
        }

        $key = safeToDb($key);
        $db = new DB_sql;
        $i = 0;
        $parameter = array();
        $multiple = 0;
        $db->query('SELECT id, multiple FROM redirect_parameter WHERE intranet_id = '.$this->kernel->intranet->get('id').' AND redirect_id = '.$this->id.' AND parameter = "'.$key.'"');
        if($db->nextRecord()) {
            $multiple = $db->f('multiple');
            $db->query('SELECT id, value, extra_value FROM redirect_parameter_value WHERE intranet_id = '.$this->kernel->intranet->get('id').' AND redirect_parameter_id = '.$db->f('id').' ORDER BY id');
            while($db->nextRecord()) {
                if($with_extra_value == 'with_extra_value') {

                    $parameter[$i]['value'] = $db->f('value');
                    $parameter[$i]['extra_value'] = $db->f('extra_value');
                } else {
                    $parameter[$i] = $db->f('value');
                }
                $i++;
            }
        }


        if($multiple == 1) {
            return $parameter;
        } else {
            if (array_key_exists(0, $parameter)) {
                return $parameter[0];
            } else {
                return '';
            }
        }
    }

    /**
     * Returns the identifier
     *
     * @todo replace all instances of calls to $redirect->get('identifier');
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->get('identifier');
    }

    /**
     * Returns the id
     *
     * @todo replace all instances of calls to $redirect->get('id');
     *
     * @return integer
     */
    public function getId()
    {
        return $this->get('id');
    }

    /**
     * Returns the redirect query string
     *
     * @todo replace all instances of calls to $redirect->get('redirect_query_string');
     *
     * @return string
     */
    public function getRedirectQueryString()
    {
        return $this->get('redirect_query_string');
    }

    /**
     * Returns the redirect query string
     *
     * @todo replace all instances of calls to $redirect->get('return_url');
     *
     * @return string
     */
    private function getReturnUrl()
    {
        return $this->get('return_url');
    }

    /**
     *
     * @todo this should be private soon
     */
    function get($key)
    {
        if (isset($this->value[$key])) {
            return $this->value[$key];
        } else {
            return '';
        }

    }
}
