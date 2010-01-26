<?php
class Intraface_modules_stock_Controller_Index extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $smarty = $this->template->create(dirname(__FILE__) . '/templates/index');
        return $smarty->render($this);
    }

    function getStock()
    {
        $this->getKernel()->useModule('product');
        $stock = new Product($this->getKernel());
        return $list = $stock->getList("stock", '', $this->query('c'));
    }

    function postForm()
    {
        foreach ($_POST['id'] AS $key=>$values) {
            /*
            NOTE!!!
            Pointen i det hele er man udv�lger et array, som man genneml�ber - i dette tilf�lde
            date - det kunne lige s� godt v�re amount - det eneste der skal bruges er $key for vi
            ved hvilken position den nuv�rende v�rdi har i POST arrayed p� det enkelte element.
            */
            $stock = new Stock(new Product($kernel, $_POST['id'][$key]));
            $stock->set($_POST['quantity'][$key]);
        }

        return new k_SeeOther($this->url());
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}