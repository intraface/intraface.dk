<?php
class Intraface_modules_shop_Controller_Categories_Show extends k_Component
{
    function getModel($id = 0)
    {
        return $this->context->getModel($id);
    }

    function map($name)
    {
        if ($name == 'edit') {
            return 'Intraface_modules_shop_Controller_Categories_Edit';
        }
    }
}