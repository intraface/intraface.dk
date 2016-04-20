<?php
class Intraface_modules_shop_Controller_BasketEvaluation_Edit extends k_Component
{
    protected $template;
    protected $mdb2;
    protected $basketevaluation;
    public $value;

    function __construct(k_TemplateFactory $template, MDB2_Driver_Common $mdb2)
    {
        $this->template = $template;
        $this->mdb2 = $mdb2;
    }

    function getBasketEvaluation()
    {
        if (is_object($this->basketevaluation)) {
            return $this->basketevaluation;
        }
        if (is_numeric($this->context->name())) {
            $this->basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->mdb2, $this->getKernel()->intranet, $this->getShop(), (int)$this->context->name());
        } else {
            $this->basketevaluation = new Intraface_modules_shop_BasketEvaluation($this->mdb2, $this->getKernel()->intranet, $this->getShop());
        }
        return $this->basketevaluation;
    }

    function getShop()
    {
        return $this->context->getShop();
    }

    function renderHtml()
    {
        if (is_numeric($this->context->name())) {
            $this->document->setTitle('Edit basket evaluation');
        } else {
            $this->document->setTitle('Create new basket evaluation');
        }

        if (!$this->body()) {
            if (is_numeric($this->context->name())) {
                $this->value = $this->getBasketEvaluation()->get();
            } else {
                $this->value = array();
            }
        }

        $settings = $this->getBasketEvaluation()->get('settings');

        $data = array('basketevaluation' => $this->getBasketEvaluation(),
                      'value' => $this->value,
                      'settings' => $settings,
                      'translation' => $this->getKernel()->getTranslation('shop')
        );
        $tpl = $this->template->create('Intraface/modules/shop/Controller/tpl/evaluation');
        return $tpl->render($this, $data);

    }

    function postForm()
    {
        if ($this->getBasketEvaluation()->save($this->body())) {
            if (is_numeric($this->context->name())) {
                return new k_SeeOther($this->url('../../'));
            } else {
                return new k_SeeOther($this->url('../'));
            }
        }
        $this->value = $this->body();

        return $this->render();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
