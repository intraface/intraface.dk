<?php
/**
 * @author lsolesen
 */
class Intraface_modules_stock_Controller_Variations extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $this->document->setTitle('Set stock for variations for product ' . $this->getProduct()->get('name'));

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/variations');
        return $smarty->render($this, array('variations' => $this->getProduct()->getVariations(), 'product' => $this->context->getProduct()));
    }

    function postForm()
    {
        // @todo - throw in some error handling
        if ($this->getProduct()->hasVariation()) {
            foreach ($this->body('variations') as $variation_id => $value) {
                $variation = $this->getProduct()->getVariation($variation_id);
                if (!$variation->getId()) {
                    throw new Exception('Invalid variation.');
                }
                if ($value <> 0) {
                    if (!$variation->getStock($this->getProduct())->regulate(array('quantity' => $value, 'description' => 'Batch edited'))) {
                        echo $this->getProduct()->error->view();
                    }
                }
            }
            return new k_SeeOther($this->url('../../'));
        }
        return $this->render();
    }

    function getProduct()
    {
        return $this->context->getProduct();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
