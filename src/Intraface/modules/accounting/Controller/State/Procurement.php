<?php
class Intraface_modules_accounting_Controller_State_Procurement extends k_Component
{
    protected $year;
    protected $value = array();
    protected $template;
    protected $add_propose_button = false;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map()
    {
        return 'Intraface_modules_accounting_Controller_State_SelectYear';
    }

    function renderHtml()
    {
        $this->getKernel()->module('procurement');
        $this->getKernel()->useModule('accounting');

        if (!$this->getYear()->readyForState($this->getModel()->get('this_date'))) {
            return new k_SeeOther($this->url('selectyear'));
        }

        $this->document->setTitle('State procurement #' . $this->getProcurement()->get('id'));

        $data = array(
            'procurement' => $this->getProcurement(),
            'year' => $this->getYear(),
            'account' => new Account($this->getYear()),
            'voucher' => new Voucher($this->getYear()),
            'items' => $this->getProcurement()->getItems(),
            'value' => $this->getValues(),
            'vat_account' => new Account($this->getYear(), $this->getYear()->getSetting('vat_in_account_id')));

        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/state/procurement');
        return $smarty->render($this, $data);
    }

    function postForm()
    {
        $this->getKernel()->module('procurement');
        $this->getKernel()->useModule('accounting');

        if ($this->body('state')) {
            if ($this->getProcurement()->checkStateDebetAccounts($this->getYear(), $this->body('debet_account'), 'do_amount_check')) {
                if ($this->getProcurement()->state($this->getYear(), $this->body('voucher_number'), $this->body('voucher_date'), $this->body('debet_account'), (int)$this->body('credit_account_number'), $this->getKernel()->getTranslation('procurement'))) {
                    return new k_SeeOther($this->url('../'));
                }
                $this->getProcurement()->error->set('Kunne ikke bogføre posten');
            } else {
                $this->add_propose_button = true;
            }
        }

        return $this->render();
    }

    function getValues()
    {
        $items_amount = 0; // @todo what is this?
        if ($this->body()) {
            $this->value = $this->body();
            if ($this->body('add_line')) {
                array_push($this->value['debet_account'], array('text' => '', 'amount' => '0,00'));
                return $this->value;
            } elseif ($this->body('remove_line')) {
                foreach ($this->body('remove_line') as $key => $void) {
                    array_splice($this->value['debet_account'], $key, 1);
                }
                return $this->value;
            } elseif ($this->body('propose_changes')) {
                $this->value['debet_account'][] = array('text' => 'Proposed solution', 'amount' => '100,00');
            }
        } else {
            $i = 0;
            $procurement = $this->getProcurement();
            $this->value = $procurement->get();
            if ($procurement->get('price_items') - $items_amount > 0) {
                $this->value['debet_account'][$i++] = array(
                    'text' => '',
                    'amount' => number_format($procurement->get('price_items') - $items_amount, 2, ',', '.'));
            }

            if ($procurement->get('price_shipment_etc') > 0) {
                $this->value['debet_account'][$i++] = array(
                    'text' => $this->t('shipment etc'),
                    'amount' => $procurement->get('dk_price_shipment_etc'));
            }
        }

        return $this->value;
    }

    function getYear()
    {
        return $this->year = new Year($this->getKernel());
    }

    function getProcurement()
    {
        return $this->context->getProcurement();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getModel()
    {
        return $object = $this->context->getModel();
    }
    
    function getProposeButton()
    {
        if ($this->add_propose_button === true) {
            return '<input type="submit" name="propose_changes" value="' . $this->t('Propose changes') . '" />';
        }
    }
}
