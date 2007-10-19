<?php
/**
 * CMS-Server
 *
 * @package CMS
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */

require_once 'XML/RPC2/Server.php';

class Intraface_XMLRPC_CMS_Server {

    private $credentials;
    private $kernel;

    function factory($site_id) {
        if (!$this->kernel->intranet->hasModuleAccess('cms')) { // -2
            throw new XML_RPC2_FaultException('Intranettet har ikke adgang til modulet cms', -2);
        }
        if (empty($site_id) OR !is_numeric($site_id)) { // -5
            throw new XML_RPC2_FaultException('Siteid er ikke gyldigt', -5);
        }
        $cms_module = $this->kernel->module('cms');
        $this->cmssite = new CMS_Site($this->kernel, $site_id);
    }

    /**
     * Gets a page
     *
     * @param struct  $credentials
     * @param integer $site_id
     * @param string  $identifier
     *
     * @return array
     */
    function getPage($credentials, $site_id, $identifier) {

        $this->checkCredentials($credentials);

        // validate
        $site_id = intval($site_id);
        $identifier = strip_tags($identifier);

        $this->factory($site_id);

        $send_array = array(
            'identifier' => $identifier,
            'site_id' => $site_id
        );
        $cmspage = CMS_Page::factory($this->cmssite->kernel, 'identifier', $send_array);
        if (!isset($cmspage) OR !is_object($cmspage) OR !$cmspage->get('id') > 0) {
            // det er muligt at dette kan have fejlsideindhold
            // måske skal man kunne vælge en side til en 404 mv., som så bare hentes i stedet.
            $values['http_header_status'] = 'HTTP/1.0 404 Not Found';
            $values['content'] = 'Siden er ikke fundet';
            $values['navigation-main'] = '';
            $values['css'] = '';
            $values['css_header'] = '';
            $values['sections'] = array(); // this could be the 404
            $values['comments'] = array();

        }
        else {

            $cmspage->value['http_header_status'] = 'HTTP/1.0 200 OK';

            /**
             * HACK HACK HACK
             * niveau 9999 gør at den ikke kan genkende den, og tager top_level.
             * 0 der ellers skulle være topmenu virker af en mærkelig grund ikke. Variablen er ikke registeret som sat!
             */
            $cmspage->value['navigation_toplevel'] = $cmspage->navigation->build(9999, 'array');	// 'toplevel'
            $cmspage->value['navigation_sublevel'] = $cmspage->navigation->build(1, 'array'); // 'sublevel'
            $cmspage->value['sections'] = $cmspage->collect();
            $cmspage->value['comments'] = $cmspage->getComments();
            $cmspage->value['css_header'] = $cmspage->cmssite->stylesheet->get('header');
            $cmspage->value['css'] = $cmspage->cmssite->stylesheet->get('css');
            $cmspage->value['content_type'] = 'text/html; charset=iso-8859-1';
            $values = $cmspage->get();
        }



        return $values;
    }

    /**
     * Gets a list with pages
     *
     * @param struct $credentials
     * @param integer $site_id
     * @param array $search
     * @return array
     */
    function getPageList($credentials, $site_id, $search = '') {

        $this->checkCredentials($credentials);

        $site_id = intval($site_id);
        $type = strip_tags($search['type']);

        $this->factory($site_id);

        $cmspage = new CMS_Page($this->cmssite);
        if (isset($search['type'])) {
            $cmspage->dbquery->setFilter('type', $search['type']);
        }

        if (isset($search['level'])) {
            $cmspage->dbquery->setFilter('level', $search['level']);
        }


        return $cmspage->getList();
    }

    /**
     * Gets a sitemap
     *
     * @param struct $credentials
     * @param integer $site_id
     * @return array
     */
    function getSitemap($credentials, $site_id) {
        $this->checkCredentials($credentials);

        $site_id = intval($site_id);

        $this->factory($site_id);

        $sitemap = new CMS_SiteMap($this->cmssite);
        return $sitemap->build();

    }

    /**
     * Checking credentials
     *
     * @param struct $credentials
     * @return array
     */
    function checkCredentials($credentials) {

        $this->credentials = $credentials;

        if ($count = count($credentials) != 2) { // -4
            throw new XML_RPC2_FaultException('Der er et forkert antal argumenter i credentials ('.$count.')', -4);
        }

        if (empty($credentials['private_key'])) { // -5
            throw new XML_RPC2_FaultException('Du skal skrive en kode', -5);
        }

        $this->kernel = new Kernel();
        if (!$this->kernel->weblogin('private', $credentials['private_key'], $credentials['session_id'])) { // -2
            throw new XML_RPC2_FaultException('Du har ikke adgang til intranettet', -2);
        }

        if (!is_object($this->kernel->intranet) AND get_class($this->kernel->intranet) != 'intranet') { // -2
            throw Exception('Du har ikke adgang til intranettet');
        }
    }

}
?>
