<?php
class Intraface_modules_accounting_Controller_Account_Index extends k_Component
{
    protected function map($name)
    {
        if ($name == 'create') {
            return 'Intraface_modules_accounting_Controller_Account_Edit';
        } elseif (is_numeric($name)) {
        	return 'Intraface_modules_accounting_Controller_Account_Show';
        } elseif ($name == 'popup') {
        	return 'Intraface_modules_accounting_Controller_Account_Popup';
        }
    }

    function GET()
    {
        $year = $this->getYear();
        $year->checkYear();

        if (!empty($_GET['action']) AND $_GET['action'] == 'delete' AND is_numeric($_GET['id'])) {
            $account = new Account($year, $_GET['id']);
            $account->delete();
        } else {
            $account = new Account($year);
            $values['from_date'] = $year->get('from_date_dk');
            $values['to_date'] = $year->get('to_date_dk');
        }

        //$accounts = $account->getSaldoList($values['from_date'], $values['to_date']);
        $accounts = $account->getList('stated', true);

        return parent::GET();
    }

    function renderHtml()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/../templates/account/index.tpl.php');
        return $smarty->render($this);
    }

    function getAccount()
    {
    	return new Account($this->getYear());
    }

    function getAccounts()
    {
    	return $this->getAccount()->getList('stated', true);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getYear($id = 0)
    {
        $year = $this->context->getYear();
        $year->checkYear();

        return $year;
    }

    function t($phrase)
    {
        return $phrase;
    }

    function renderXls()
    {
        $year = new Year($kernel);
        $year->checkYear();

        $account = new Account($year);

        $values['from_date'] = $year->get('from_date_dk');
        $values['to_date'] = $year->get('to_date_dk');

        $accounts = $this->getAccounts();

        $workbook = new Spreadsheet_Excel_Writer();

        // sending HTTP headers
        $workbook->send($kernel->intranet->get('name') . ' - konti ' . $year->get('label'));

        // Creating a worksheet
        $worksheet =& $workbook->addWorksheet('Konti ' . $year->get('label'));

        $format_bold =& $workbook->addFormat();
        $format_bold->setBold();
        $format_bold->setSize(8);

        $format_italic =& $workbook->addFormat();
        $format_italic->setItalic();
        $format_italic->setSize(8);

        $format =& $workbook->addFormat();
        $format->setSize(8);

        $i = 0;
        $worksheet->write($i, 0, $kernel->intranet->get('name'), $format_bold);

        $i = 2;
        if (count($accounts) > 0) {
        	foreach ($accounts AS $account) {
        		$style = '';
        		if ($account['type'] == 'headline') {
        			$style = $format_bold;
        		} elseif ($account['type'] == 'sum') {
        			$style = $format_italic;
        		} else {
        			$style = $format;
        		}

        		$worksheet->write($i, 0, $account['number'], $style);
        		$worksheet->write($i, 1, $account['name'], $style);
        		$worksheet->write($i, 2, $account['type'], $style);
        		if ($account['type'] != 'Headline') {
        			$worksheet->write($i, 3, abs(round($account['saldo'])), $style);
        		}
        		$i++;
        	}
        }
        $worksheet->hideGridLines();

        // Let's send the file
        $workbook->close();
    }

    function renderHtmlCreate()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/../templates/account/edit.tpl.php');
        return $smarty->render($this);
    }

    function postForm()
    {
        $year = new Year($this->getKernel());
        $year->checkYear();

        $account = new Account($year);

        if (isset($_POST['vat_key']) && $_POST['vat_key'] != 0) {
            $_POST['vat_percent'] = 25;
        }

        if ($id = $account->save($_POST)) {
            return new k_SeeOther($this->url($id));
        } else {
            $values = $_POST;
        }
        return $this->render();
        /*
        if ($id = $this->getYear()->save($_POST)) {
            return new k_SeeOther($this->url('../'));
        } else {
            $values = $_POST;
            $values['from_date_dk'] = $_POST['from_date'];
            $values['to_date_dk'] = $_POST['to_date'];
            return $this->render();
        }
        */
    }

    function getValues()
    {
        return $this->body();
    }
}