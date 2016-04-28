<?php
/**
 * Intraface_Doctrine_Template_Intranet
 *
 * @package     Intraface
 * @subpackage  Intraface_Doctrine
 * @author      Lars Olesen <lars@legestue.net>
 */
class Intraface_Doctrine_Template_Intranet extends Doctrine_Template
{
    /**
     * @return void
     */
    public function setTableDefinition()
    {
        $this->hasColumn('intranet_id', 'integer', 11);
        require_once 'Intraface/Doctrine/Template/Listener/Intranet.php';
        $this->addListener(new Intraface_Doctrine_Template_Listener_Intranet());
    }
}
