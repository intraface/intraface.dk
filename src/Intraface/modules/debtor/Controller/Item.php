<?php
class Intraface_modules_debtor_Controller_Item extends k_Component
{
    function renderHtml()
    {
        return 'Intentionally left blank';
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
        	$url = $redirect->setDestination($product_module->getPath().'select_product.php', $debtor_module->getPath().'item_edit.php?debtor_id='.$debtor->get('id').'&id='.$debtor->item->get('id'));
        	$redirect->askParameter('product_id');
        	header('location: '.$url);
        	exit;
        } elseif (isset($_GET['return_redirect_id'])) {
        	$redirect = Intraface_Redirect::factory($this->getKernel(), 'return');
            $returned_values = unserialize($redirect->getParameter('product_id'));
        	$debtor->item->changeProduct($returned_values['product_id'], $returned_values['product_variation_id']);
            $debtor->loadItem(intval($_GET["id"]));
        }

        $smarty = new k_Template(dirname(__FILE__) . '/templates/item-edit.tpl.php');
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

    function t($phrase)
    {
        return $phrase;
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
}
