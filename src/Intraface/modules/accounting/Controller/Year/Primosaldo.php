<?php
class Intraface_modules_accounting_Controller_Year_Primosaldo extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $year = $this->getYear();
        $account = new Account($year);

        $accounts = $account->getList('balance');

        $total_debet = 0;
        $total_credit = 0;

        $data = array('total_debet' => $total_debet, 'total_credit' => $total_credit, 'account' => $account, 'year' => $year, 'accounts' => $accounts);

        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/year/primosaldo');
        return $smarty->render($this, $data);
    }

    function postForm()
    {
        $year = new Year($this->getKernel(), $this->context->name());

        if ($year->get('last_year_id') == 0) {
            throw new Exception('No last year set');
        }

        // oprette objekt til at holde sidste �r
        $last_year = new Year($this->getKernel(), $year->get('last_year_id'));

        // hente konti hvor de nye har created_from_id
        $account = new Account($year);
        $accounts = $account->getList('balance');
        foreach ($accounts AS $a) {
            //  @todo Should test whether all posts in the past year has been stated
            $old_account = new Account($last_year, $a['created_from_id']);
            $saldo = $old_account->getSaldo('stated');

            if ($old_account->get('credit') == $old_account->get('debet')) {
                $saldo = array(
                    'credit' => 0,
                    'debet' => 0
                );
            } elseif ($old_account->get('credit') > $old_account->get('debet')) {
                $saldo = array(
                    'credit' => $old_account->get('credit') - $old_account->get('debet'),
                    'debet' => 0
                );
            } elseif ($old_account->get('credit') < $old_account->get('debet')) {
                $saldo = array(
                    'credit' => 0,
                    'debet' => $old_account->get('debet') - $old_account->get('credit')
                );
            }

            $account = new Account($year, $a['id']);
            $account->savePrimosaldo(number_format($saldo['debet'], 2, ',', ''), number_format($saldo['credit'], 2, ',', ''));
            return new k_SeeOther($this->url());
        }
        return $this->render();
    }

    function renderHtmlEdit()
    {
        $this->getKernel()->module('accounting');

      	$year = new Year($this->getKernel(), $this->context->name());

        $account = new Account($year);
        $accounts = $account->getList('balance');

        $total_debet = 0;
        $total_credit = 0;

        $data = array(
            'total_debet' => 0,
            'total_credit' => 0,
            'accounts' => $accounts,
            'year' => $year,
            'account' => $account
        );

        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/year/primosaldo-edit');
        return $smarty->render($this, $data);

    }

    function putForm()
    {
        $year = $this->getYear();
        foreach ($_POST['id'] AS $key=>$values) {
        	$account = new Account($year, $_POST['id'][$key]);
        	$account->savePrimosaldo($_POST['debet'][$key], $_POST['credit'][$key]);
        }
        if (!$account->error->isError()) {
        	return new k_SeeOther($this->url());
       	}
        return $this->render();
    }

    function getYear()
    {
        return $this->context->getYear();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}