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
    protected $db;

    function __construct(k_TemplateFactory $template, DB_Sql $db)
    {
        $this->template = $template;
        $this->db = $db;
    }

    function renderHtml()
    {
        $this->document->setTitle('Switch intranet');

        if ($this->getKernel()->user->hasIntranetAccess($this->query('id'))) {
            // @todo make sure a new user is stored in Auth, otherwise
            //       the access to the modules are not correctly maintained.
            //       Right now I just clear permisions when getting the new user
            //       which probably is the most clever solution.
        	if ($this->getKernel()->user->setActiveIntranetId(intval($this->query('id')))) {
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
        $this->db->query("SELECT DISTINCT(intranet.id), name FROM intranet INNER JOIN permission ON permission.intranet_id = intranet.id WHERE permission.user_id = " . $this->getKernel()->user->getId() . " ORDER BY name");
        $accessible_intranets = array();
        while ($this->db->nextRecord()) {
            $accessible_intranets[$this->db->f('id')] = $this->db->f('name');
        }

        return $accessible_intranets;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}