<?php
class Intraface_modules_administration_Controller_Intranets extends k_Component
{
    function map($name)
    {
        return 'Intraface_modules_administration_Controller_Intranet';
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}