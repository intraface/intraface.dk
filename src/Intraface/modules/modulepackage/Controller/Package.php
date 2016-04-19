<?php
class Intraface_modules_modulepackage_Controller_Package extends k_Component
{
    function map($name)
    {
        return 'Intraface_modules_modulepackage_Controller_AddPackage';
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
