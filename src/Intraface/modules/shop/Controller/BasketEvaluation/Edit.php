<?php
class Intraface_modules_shop_Controller_BasketEvaluation_Edit extends k_Component
{
    protected $template;
    protected $mdb2;

    function __construct(k_TemplateFactory $template, MDB2_Driver_Common $mdb2)
    {
        $this->template = $template;
        $this->mdb2 = $mdb2;
    }

    function getShop()
    {
        return $this->context->getShop();
    }

    function renderHtml()
    {
        if (is_numeric($this->context->name())) {
            $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->mdb2, $this->getKernel()->intranet, (int)$this->context->name());
            $value = $basketevaluation->get();
            $this->document->setTitle('Edit basket evaluation');
        } else {
            $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->mdb2, $this->getKernel()->intranet, $this->getShop());
            $value = array();
            $this->document->setTitle('Create new basket evaluation');
        }

        $settings = $basketevaluation->get('settings');

        $data = array('basketevaluation' => $basketevaluation,
                      'value' => $value,
                      'settings' => $settings,
                   	  'translation' => $this->getKernel()->getTranslation('shop')
        );
        $tpl = $this->template->create('Intraface/modules/shop/Controller/tpl/evaluation');
        return $tpl->render($this, $data);

    }

    function postForm()
    {
        if (is_numeric($this->context->name())) {
            $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->mdb2, $this->getKernel()->intranet, $this->getShop(), (int)$this->context->name());
        } else {
            $basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->mdb2, $this->getKernel()->intranet, $this->getShop());
        }
        if (!$basketevaluation->save($this->body())) {
            throw new Exception('Could not save values');
        }

        if (is_numeric($this->context->name())) {
            return new k_SeeOther($this->url('../../'));
        } else {
            return new k_SeeOther($this->url('../'));
        }

        return $this->render();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}