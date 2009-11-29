<?php
class Intraface_modules_product_Controller_AttributeGroups_Show extends k_Component
{
    private $error;
    private $group;
    private $attribute;
    
    public function map($name) {
        return 'Intraface_modules_product_Controller_AttributeGroups_Attribute';
    }
    
    function getError()
    {
        if(!is_object($this->error)) {
            $this->error = new Intraface_Doctrine_ErrorRender($this->getKernel()->getTranslation('product'));
        }

        return $this->error;
    }
    
    function getKernel()
    {
        return $this->context->getKernel();
    }
    
    public function getGroup()
    {
        if(!is_object($this->group)) {
            $gateway = new Intraface_modules_product_Attribute_Group_Gateway();
            $this->group = $gateway->findById($this->name()); 
        }
        
        return $this->group;
    }
    
    private function getAttribute()
    {
        if(!is_object($this->attribute)) {
            $this->attribute = new Intraface_modules_product_Attribute;
            $this->attribute->attribute_group_id = $this->getGroup()->getId();
        }
        
        return $this->attribute;
    } 
    
    function postForm()
    {
        Intraface_Doctrine_Intranet::singleton($this->context->getKernel()->intranet->getId());

        $group = $this->getGroup();

        if($this->subview() == 'edit') {
            $group->name = $_POST['name'];
            $group->description = $_POST['description'];
            try {
                $group->save();
                $group->load();
                return new k_SeeOther($this->url());
            } catch(Doctrine_Validator_Exception $e) {
                $error = new Intraface_Doctrine_ErrorRender($translation);
                $error->attachErrorStack($group->getErrorStack());
            }
        }
        
        if($this->subview() == 'create') {
            // $attribute = $group->attribute[0];
            $attribute = $this->getAttribute();
    
            $attribute->name = $_POST['name'];
        
            try {
                $attribute->save();
                return new k_SeeOther($this->url());
            }
            catch (Doctrine_Validator_Exception $e) {
                $this->attribute = $attribute;
                $this->getError()->attachErrorStack($attribute->getErrorStack());
            }
        }
        
        return $this->render();
    
    }

    function renderHtmlEdit()
    {
        Intraface_Doctrine_Intranet::singleton($this->context->getKernel()->intranet->getId());

        $group = $this->getGroup();
        $attributes = $group->getAttributes();
        $data = array('group' => $group);

        $smarty = new k_Template('Intraface/modules/product/Controller/tpl/attributegroup-edit.tpl.php');
        return $smarty->render($this, $data);
    }

    function renderHtml()
    {

        $group = $this->getGroup();
        $attributes = $group->getAttributes();

        $data = array('group' => $group, 'attributes' => $attributes);
        $smarty = new k_Template('Intraface/modules/product/Controller/tpl/attributegroup.tpl.php');
        return $smarty->render($this, $data);
    }
    
    function renderHtmlCreate()
    {
        $data = array(
            'group' => $this->getGroup(),
        );
        if(is_object($this->attribute)) {
            $data['attribute'] = $this->getAttribute();
        }
        
        $smarty = new k_Template('Intraface/modules/product/Controller/tpl/attribute-edit.tpl.php');
        return $smarty->render($this, $data);

    }
    
    function renderHtmlDelete()
    {
        $smarty = new k_Template('Intraface/Controller/templates/delete.tpl.php');
        return $smarty->render($this);
    }

    function DELETE()
    {
        if ($id = $this->getGroup()->delete()) {
            return new k_SeeOther($this->url('../'));
        }
    }
    
}