<?php
/**
 * Redirects a user to specific pages
 *
 * @package Intraface
 * @author  Sune Jensen <sj@sunet.dk>
 * @version @package-version@
 */

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
 * if (isset($_POST['submit'])) {
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
 * if ($go_further) {
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
 * if (isset($_GET['return_redirect_id'])) {
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
class Intraface_Redirect extends Ilib_Redirect
{
    /**
     * Constructs a redirect object
     *
     * @param object  $kernel kernel
     * @param integer $id     Id of the redirect
     *
     * @return object
     */
    public function __construct($kernel, $id = 0)
    {
        $options = array(
             'extra_db_condition' => array('intranet_id = '.$kernel->intranet->get('id'))
        );

        $db = MDB2::singleton(DB_DSN);

        parent::__construct($kernel->getSessionId(), $db, $id, $options);
    }

    /**
     * Creates a redirect object on the go page
     *
     * @param object $kernel kernel
     * @param string $query_variable the variable used in the querystring for going to the redirect page
     * @param string $query_return_variable the variable  used in the querystring when returning from the redirect page.
     *
     * @return object
     */
    static function go($kernel, $query_variable = 'redirect_id', $query_return_variable = 'return_redirect_id')
    {
        return self::factory($kernel, 'go', $query_variable, $query_return_variable);
    }

    /**
     * Creates a redirect object on the receiving page
     *
     * @param object $kernel kernel
     * @param string $query_variable the variable used in the querystring for going to the redirect page
     * @param string $query_return_variable the variable  used in the querystring when returning from the redirect page.
     *
     * @return object
     */
    static function receive($kernel, $query_variable = 'redirect_id', $query_return_variable = 'return_redirect_id')
    {
        return self::factory($kernel, 'receive', $query_variable, $query_return_variable);
    }

    /**
     * Creates a redirect object on the returning page
     *
     * @param object $kernel kernel
     * @param string $query_variable the variable used in the querystring for going to the redirect page
     * @param string $query_return_variable the variable  used in the querystring when returning from the redirect page.
     *
     * @return object
     */
    static function returns($kernel, $query_variable = 'redirect_id', $query_return_variable = 'return_redirect_id')
    {
        return self::factory($kernel, 'return', $query_variable, $query_return_variable);
    }

    /**
     * Creates a redirect object
     *
     * This should be substituted with specific methods for the types
     *
     * @param object $kernel kernel
     * @param string $type Can be either go (starting a redirect), receive (on the destination page for redirect) or return (when returning after a redirect)
     * @param string $query_variable the variable used in the querystring for going to the redirect page
     * @param string $query_return_variable the variable  used in the querystring when returning from the redirect page.
     *
     * @return object
     */
    static function factory($kernel, $type, $query_variable = 'redirect_id', $query_return_variable = 'return_redirect_id')
    {
        if (!is_object($kernel)) {
            trigger_error("First parameter in redirect::factory is not kernel", E_USER_ERROR);
        }

        $options = array(
            'extra_db_condition' => array('intranet_id = '.$kernel->intranet->get('id')),
            'query_variable' => $query_variable,
            'query_return_variable' => $query_return_variable
        );

        $db = MDB2::singleton(DB_DSN);

        return parent::factory($kernel->getSessionId(), $db, $type, $options);
    }
}