<?php
/**
 * Switch intranet
 *
 * @package Intraface
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */
class Intraface_Controller_SwitchIntranet extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $this->document->setTitle('Switch intranet');

        if (isset($_GET["id"]) && $this->getKernel()->user->hasIntranetAccess($_GET['id'])) {
            // @todo make sure a new user is stored in Auth, otherwise
            //       the access to the modules are not correctly maintained.
            //       Right now I just clear permisions when getting the new user
            //       which probably is the most clever solution.
        	if ($this->getKernel()->user->setActiveIntranetId(intval($_GET['id']))) {
        		return new k_SeeOther($this->url('../'));
        	} else {
        		throw new Exception('Could not change intranet');
        	}
        }

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/switchintranet');
        return $smarty->render($this);
    }

    function getIntranets()
    {
        // @todo b�r hente en liste vha. intranethall�jsaen
        $db = new DB_Sql;
        $db->query("SELECT DISTINCT(intranet.id), name FROM intranet INNER JOIN permission ON permission.intranet_id = intranet.id WHERE permission.user_id = " . $this->getKernel()->user->getId() . " ORDER BY name");
        $accessible_intranets = array();
        while ($db->nextRecord()) {
            /*
            if (!$this->getKernel()->user->hasIntranetAccess($db->f("id"))) {
                continue;
            }
            */
            $accessible_intranets[$db->f('id')] = $db->f('name');
        }

        return $accessible_intranets;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}