<?php
class Intraface_modules_shop_Controller_Categories_Edit extends k_Controller
{
    function getShopId()
    {
        return $this->context->context->getShopId();
    }
    
    function getModel()
    {
        return new Ilib_Category($this->registry->get('db'), 
            new Intraface_Category_Type('shop', $this->getShopId()), 
            $this->getId());
    }
    
    function getId()
    {
        if (is_numeric($this->context->name)) {
            return $this->context->name;
        } else {
            return 0;
        }
    }
    
    function GET()
    {
        $this->document->title = $this->__('Edit category');
        
        $data = array(
            'category_object' => $this->getModel()
        );
        return $this->render(dirname(__FILE__) . '/../tpl/categories-edit.tpl.php', $data);
    }   
    
    function POST()
    {
         if (!$this->isValid()) {
            throw new Exception('Values not valid');
        }
        try {
            $category = $this->getModel();
            $category->setIdentifier($this->POST['identifier']);
            $category->setName($this->POST['name']);
            $category->setParentId($this->POST['parent_id']);
            $category->save();
        } catch (Exception $e) {
            throw $e;
        }        
        if ($this->getId() == 0) {
            $url = $this->context->url();
        } else {
            $url = $this->context->context->url();
        }
        
        throw new k_http_Redirect($url);
    } 

    function isValid()
    {
        $error = new Intraface_Error();
        $validator = new Intraface_Validator($error);
        $validator->isString($this->POST['name'], 'category name is not valid');
        $validator->isString($this->POST['identifier'], 'category identifier is not valid');
        return !$error->isError();
    }
}