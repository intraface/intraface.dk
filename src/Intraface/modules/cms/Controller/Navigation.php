<?php
class Intraface_modules_cms_Controller_Navigation extends k_Component
{
    protected $template;
    protected $mdb2;

    function __construct(MDB2_Driver_Common $mdb2, k_TemplateFactory $template)
    {
        $this->mdb2 = $mdb2;
        $this->template = $template;
    }

    function renderHtml()
    {
        $webshop_module = $this->getKernel()->module('cms');

        $this->document->setTitle('Navigation for ' . $this->getSite()->get('name'));

        $data['categories'] = $this->getNavigationGateway()->findBySite($this->getSite());

        $tpl = $this->template->create('Intraface/modules/cms/Controller/templates/navigation');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');

        if (!$this->isValid()) {
            throw new Exception('Values not valid');
        }
        try {
            $category = $this->getModel();
            $category->setIdentifier($this->body('identifier'));
            $category->setName($this->body('name'));
            $category->setParentId($this->body('parent_id'));
            $category->save();
        } catch (Exception $e) {
            throw $e;
        }
        if ($this->getId() == 0) {
            $url = $redirect->getRedirect($this->context->url());
        } else {
            $url = $redirect->getRedirect($this->context->context->url());
        }

        return new k_SeeOther($redirect->getRedirect($url));
    }

    function isValid()
    {
        $error = new Intraface_Error();
        $validator = new Intraface_Validator($error);
        $validator->isString($this->body('name'), 'category name is not valid');
        $validator->isString($this->body('identifier'), 'category identifier is not valid');
        $validator->isNumeric($this->body('parent_id'), 'category parent id has to be numeric');
        return !$error->isError();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getSite()
    {
        return $this->context->getSite();
    }

    function getNavigationGateway()
    {
        return new Intraface_modules_cms_Menu($this->getKernel());
    }
}
