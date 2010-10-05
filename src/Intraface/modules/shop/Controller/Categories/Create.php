<?php
class Intraface_modules_shop_Controller_Categories_Create extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function getShopId()
    {
        return $this->context->context->getShopId();
    }

    function getModel()
    {
        /*
        return new Ilib_Category($this->registry->renderHtml('db'),
            new Intraface_Category_Type('shop', $this->getShopId()),
            $this->getId());
        */
        return $this->context->getModel($this->getId());
    }

    function getId()
    {
        if (is_numeric($this->context->name())) {
            return $this->context->name;
        } else {
            return 0;
        }
    }

    function renderHtml()
    {
        $this->document->setTitle('Edit category');
        $redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');

        $data = array(
            'category_object' => $this->getModel(),
            'regret_link' => $redirect->getRedirect($this->url('../'))
        );
        $tpl = $this->template->create(dirname(__FILE__) . '/../tpl/categories-edit');
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
}