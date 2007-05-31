<?php
/**
 * Webshop_HTML_Parser
 *
 * This class will parse the content of the array returned from the XML-RPC
 * to valid XHTML 1.0.
 *
 * Example:
 * -------
 *
 * // getting the array from your XML-RPC-client
 * $products = $client->getProducts();
 *
 * // putting the array into the parser
 * $html = new Product_HTML_Parser();
 * $content = $html->parseProducts($products);
 *
 * If you are not satisfied with the returned result from the class, you
 * can make your own parser-functions by extending this class with your own custom
 * methods:
 *
 * Example:
 * -------
 *
 * class MyHTMLParser extends Webshop_HTML_Parser {
 *		function parseProducts($products) {
 *			foreach ($products AS $product) {
 *				return '<div class="my-own-class">' . $product['title'] . '</div>;
 *			}
 *		}
 * }
 *
 * Never rewrite the main class, as it would be much harder to upgrade to a new
 * one, when we make new functions in the cms system.
 *
 * @author		Lars Olesen <lars@legestue.net>
 * @version	1.0
 *
 * This software is released under Creative Commons / Share A Like license (by-sa):
 * http://creativecommons.org/licenses/by-sa/2.5/
 * http://creativecommons.org/licenses/by-sa/2.5/legalcode
 *
 *
 */

$HTTP_RAW_POST_DATA = file_get_contents('php://input');
class Webshop_HTML_Parser {

    /**
     * Constructor
     *
     * @param	array	$page_array	Array with information about a page
     */
    function Webshop_HTML_Parser() {
        CMS_HTML_Parser::__construct();
    }

    function __construct() {
    }

    /****************************************************************************
     * Products
     ****************************************************************************/

    /**
     */



}
if (dirname(__FILE__) . basename(__FILE__) == dirname($_SERVER['PHP_SELF']) . basename($_SERVER['PHP_SELF']) OR !empty($_GET['show_source']) AND $_GET['show_source'] == true) {
    highlight_string(file_get_contents(__FILE__));
}
?>