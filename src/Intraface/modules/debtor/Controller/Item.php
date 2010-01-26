<?php
class Intraface_modules_debtor_Controller_Item extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        if ($name == 'selectproduct') {
            return 'Intraface_modules_product_Controller_Selectproduct';
        }
    }

    function renderHtml()
    {
        return $this->renderHtmlEdit();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function renderHtmlEdit()
    {
        $product_module = $this->getKernel()->useModule('product');

        if (isset($_GET['change_product'])) {
        	$redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
        	$url = $redirect->setDestination(NET_SCHEME . NET_HOST . $this->url('selectproduct'), NET_SCHEME . NET_HOST . $this->url());
        	$redirect->askParameter('product_id');
        	return new k_SeeOther($url);
        } elseif (isset($_GET['return_redirect_id'])) {
        	$redirect = Intraface_Redirect::factory($this->getKernel(), 'return');
            $returned_values = unserialize($redirect->getParameter('product_id'));
        	$debtor->item->changeProduct($returned_values['product_id'], $returned_values['product_variation_id']);
            $debtor->loadItem(intval($_GET["id"]));
        }

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/item-edit');
        return $smarty->render($this);
    }

    function getValues()
    {
        $debtor = $this->context->getDebtor();
        $debtor->loadItem(intval($this->name()));
        $values = $debtor->item->get();
        $values["quantity"] = number_format($debtor->item->get('quantity'), 2, ",", ".");
        $values['description'] = $debtor->item->get('description');
        return $values;
    }

    function getDebtor()
    {
        return $this->context->getDebtor();
    }

    function postForm()
    {
       	$debtor = $this->context->getDebtor();
       	$debtor->loadItem(intval($_POST["id"]));

       	if ($id = $debtor->item->save($_POST)) {
       		return new k_SeeOther($this->url('../../'));
       	} else {
       		$values = $_POST;
       	}
       	return $this->render();
    }

    function addItem($returned_values)
    {
        $this->getDebtor()->loadItem($this->name());
        $this->getDebtor()->item->changeProduct($returned_values['product_id'], $returned_values['product_variation_id']);
        return true;
    }
}
