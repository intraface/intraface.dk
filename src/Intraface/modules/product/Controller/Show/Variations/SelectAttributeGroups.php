<?php
class Intraface_modules_product_Controller_Show_Variations_SelectAttributeGroups extends Intraface_modules_product_Controller_AttributeGroups
{
    protected $template;
    public $existing_groups;
    public $error;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function checkForAddedVariations()
    {
        $product = $this->context->context->getProduct();
        $this->error = new Intraface_Error;
        $this->existing_groups = array();
        foreach ($product->getAttributeGroups() AS $group) {
            $this->existing_groups[] = $group['id'];
        }
        if (count($this->existing_groups) > 0) {
            try {
                $variations = $product->getVariations();
                if ($variations->count() > 0) {
                    $this->error->set('You cannot change the attached attribute groups when variations has been created');
                    return true;
                }
            } catch (Intraface_Gateway_Exception $e) {

            }
        }

        return true;
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module('product');
        $translation = $this->getKernel()->getTranslation('product');
        Intraface_Doctrine_Intranet::singleton($this->getKernel()->intranet->getId());

        $gateway = new Intraface_modules_product_Attribute_Group_Gateway();

        $groups = $gateway->findAll();

        $res = $this->checkForAddedVariations();

        if ($res === true) {
            $tpl = $this->template->create('Intraface/modules/product/Controller/tpl/select-attribute-groups-table');
        } else {
            $tpl = $this->template->create('Intraface/modules/product/Controller/tpl/select-attribute-groups-form');
        }

        $data = array(
            'groups' => $groups,
        );

        $content = $tpl->render($this, $data);

        $data = array(
        	'groups' => $groups,
            'error' => $this->error,
            'content' => $content
        );

        $smarty = $this->template->create('Intraface/modules/product/Controller/tpl/select-attribute-groups');
        return $smarty->render($this, $data);

    }

    function putForm()
    {
        $this->checkForAddedVariations();

        if ($this->error->isError() == 0) {
            if (isset($_POST['selected']) && is_array($_POST['selected'])) {
                $new_groups = $_POST['selected'];
            }

            $product = $this->context->context->getProduct();
            $existing_groups = array();
            foreach ($product->getAttributeGroups() AS $group) {
                $existing_groups[] = $group['id'];
            }

            if (is_array($existing_groups) and is_array($new_groups)) {
                foreach (array_diff($existing_groups, $new_groups) AS $id) {
                    $product->removeAttributeGroup($id);
                }
            }

            if (is_array($new_groups)) {
                foreach ($new_groups AS $id) {
                    $product->setAttributeGroup($id);
                }
            }

            return new k_SeeOther($this->url('../', array('edit')));
        }
        return $this->render();
    }
}
