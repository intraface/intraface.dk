<?php
class Intraface_modules_accounting_Controller_Year_Primosaldo extends k_Component
{
    function postForm()
    {
        $year = new Year($kernel, $_POST['id']);

        if ($year->get('last_year_id') == 0) {
            throw new Exception('No last year set');
        }

        // oprette objekt til at holde sidste �r
        $last_year = new Year($kernel, $year->get('last_year_id'));

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

        }
    }

    function renderHtml()
    {
        $year = new Year($kernel, $_GET['id']);
        $account = new Account($year);

        $accounts = $account->getList('balance');


        $total_debet = 0;
        $total_credit = 0;

        $data = array('total_debet' => $total_debet, 'total_credit' => $total_credit, 'account' => $account, 'year' => $year, 'accounts' => $accounts);

        $smarty = new k_Template(dirname(__FILE__) . '/../templates/year/index.tpl.php');
        return $smarty->render($this, $data);

    }
}