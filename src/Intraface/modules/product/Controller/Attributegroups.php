<?php
class Intraface_modules_product_Controller_Attributegroups extends k_Component
{

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function t($phrase)
    {
        return $phrase;
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module('product');
        $translation = $this->getKernel()->getTranslation('product');
        Intraface_Doctrine_Intranet::singleton($this->getKernel()->intranet->getId());

        $gateway = new Intraface_modules_product_Attribute_Group_Gateway();

        $groups = $gateway->findAll();

        $data = array('groups' => $groups);

        $smarty = new k_Template(dirname(__FILE__) . '/tpl/attributegroups.tpl.php');
        return $smarty->render($this, $data);

    }

    function map($name)
    {
        return 'Intraface_modules_product_Controller_Attributegroup';
    }

    function postForm()
    {
        Intraface_Doctrine_Intranet::singleton($this->getKernel()->intranet->getId());

        $gateway = new Intraface_modules_product_Attribute_Group_Gateway();

        if ($this->body('action') == 'delete') {
            $deleted = array();
            if (is_array($this->body('selected'))) {
                foreach ($this->body('selected') AS $id) {
                    try {
                        $group = $gateway->findById($id);
                        $group->delete();
                        $deleted[] = $id;
                    } catch (Intraface_Gateway_Exception $e) {/* we do nothing */ }
                }
            }
        }

        if (!empty($_POST['undelete'])) {
            $undelete = (array)unserialize(base64_decode($_POST['deleted']));
            foreach ($undelete AS $id) {
                try {
                    $group = $gateway->findDeletedById($id);
                    $group->undelete();
                }
                catch (Intraface_Gateway_Exception $e) { }
            }
        }

        if($this->body('save') != '') {
            $group = new Intraface_modules_product_Attribute_Group;
            $group->name = $_POST['name'];
            $group->description = $_POST['description'];
            try {
                $group->save();
                $group->load();
                return new k_SeeOther($this->url($group->getId()));
            } catch(Doctrine_Validator_Exception $e) {
                $error = new Intraface_Doctrine_ErrorRender($this->getKernel()->getTranslation('product'));
                $error->attachErrorStack($group->getErrorStack());
            }
        }
        
        return $this->render();
    }

    function renderHtmlCreate()
    {
        $data = array();
        $smarty = new k_Template(dirname(__FILE__) . '/tpl/attributegroup-edit.tpl.php');
        return $smarty->render($this, $data);

    }
}