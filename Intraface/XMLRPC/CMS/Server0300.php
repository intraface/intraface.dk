<?php
/**
 * CMS-Server2
 *
 * @package CMS
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */
class Intraface_XMLRPC_CMS_Server0300 extends Intraface_XMLRPC_Server 
{
    private function factory($site_id) 
    {
        if (!$this->kernel->weblogin->hasModuleAccess('cms')) { // -2
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('The intranet does not have access to the cms module', -2);
        }
        if (empty($site_id) OR !is_numeric($site_id)) { // -5
            require_once 'XML/RPC2/Exception.php';
            throw new XML_RPC2_FaultException('Invalid site id supplied', -5);
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
    public function getPage($credentials, $site_id, $identifier) 
    {
        $identifier = $this->processRequestData($identifier);
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

        } else {

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

        return $this->prepareResponseData($values);
    }

    /**
     * Gets a list with pages
     *
     * @param struct $credentials
     * @param integer $site_id
     * @param array $search
     * @return array
     */
    public function getPageList($credentials, $site_id, $search = '') 
    {
        $search = $this->processRequestData($search);
        
        $this->checkCredentials($credentials);
        $site_id = intval($site_id);

        $this->factory($site_id);

        $cmspage = new CMS_Page($this->cmssite);
        if (isset($search['type'])) {
            $search['type'] = strip_tags($search['type']);
            $cmspage->getDBQuery()->setFilter('type', $search['type']);
        }

        if (isset($search['level'])) {
            $cmspage->getDBQuery()->setFilter('level', $search['level']);
        }

        return $this->prepareResponseData($cmspage->getList());
    }

    /**
     * Gets a sitemap
     *
     * @param struct $credentials
     * @param integer $site_id
     * @return array
     */
    public function getSitemap($credentials, $site_id) 
    {
        $this->checkCredentials($credentials);

        $site_id = intval($site_id);

        $this->factory($site_id);

        $sitemap = new CMS_SiteMap($this->cmssite);
        return $this->prepareResponseData($sitemap->build());

    }
}
