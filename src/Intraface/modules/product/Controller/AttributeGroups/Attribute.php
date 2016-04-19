<?php
class Intraface_modules_product_Controller_AttributeGroups_Attribute extends k_Component
{
    private $error;
    private $attribute;
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function getError()
    {
        if (!is_object($this->error)) {
            $this->error = new Intraface_Doctrine_ErrorRender($this->getKernel()->getTranslation('product'));
        }

        return $this->error;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    private function getGroup()
    {
        return $this->context->getGroup();
    }

    private function getAttribute()
    {
        if (!is_object($this->attribute)) {
            $this->attribute = $this->getGroup()->getAttribute($this->name());
        }

        return $this->attribute;
    }

    function postForm()
    {
        if ($this->subview() == 'edit') {
            $attribute = $this->getAttribute();

            $attribute->name = $_POST['name'];

            try {
                $attribute->save();
                return new k_SeeOther($this->url());
            } catch (Doctrine_Validator_Exception $e) {
                $this->attribute = $attribute;
                $this->getError()->attachErrorStack($attribute->getErrorStack());
            }
        }
        return $this->render();
    }

    function renderHtmlEdit()
    {
        $data = array(
            'group' => $this->getGroup(),
            'attribute' => $this->getAttribute()
        );

        $smarty = $this->template->create('Intraface/modules/product/Controller/tpl/attribute-edit');
        return $smarty->render($this, $data);
    }

    function renderHtml()
    {
        return new k_SeeOther($this->url('../'));
    }

    function renderHtmlDelete()
    {
        $smarty = $this->template->create('Intraface/Controller/templates/delete');
        return $smarty->render($this);
    }

    function DELETE()
    {
        if ($id = $this->getAttribute()->delete()) {
            return new k_SeeOther($this->url('../'));
        }

        throw new Exception('Could not delete');
    }
}
