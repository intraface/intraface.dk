<?php
class Intraface_modules_accounting_Controller_Account_Index extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    protected function map($name)
    {
        if (is_numeric($name)) {
        	return 'Intraface_modules_accounting_Controller_Account_Show';
        } elseif ($name == 'popup') {
        	return 'Intraface_modules_accounting_Controller_Account_Popup';
        }
    }

    function renderHtml()
    {
        $this->document->setTitle('Accounts');

        $accounts = $this->getAccount()->getList('saldo', true);

        $data = array(
            'accounts' => $accounts
        );

        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/account/index');
        return $smarty->render($this, $data);
    }

    function renderXls()
    {
        $kernel = $this->getKernel();
        $year = $this->getYear();

        $values['from_date'] = $year->get('from_date_dk');
        $values['to_date'] = $year->get('to_date_dk');

        $accounts = $this->getAccounts();

        $workbook = new Spreadsheet_Excel_Writer();
        $workbook->setVersion(8);

        // sending HTTP headers
        $workbook->send($kernel->intranet->get('name') . ' - konti ' . $year->get('label'));

        // Creating a worksheet
        $worksheet =& $workbook->addWorksheet('Konti ' . $year->get('label'));
        $worksheet->setInputEncoding('UTF-8');

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

        $worksheet->hideGridLines();

        // Let's send the file
        $workbook->close();
        exit;
    }

    function renderHtmlCreate()
    {
        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/account/edit');
        return $smarty->render($this);
    }

    function postForm()
    {
        $account = new Account($this->getYear());

        if (isset($_POST['vat_key']) && $_POST['vat_key'] != 0) {
            $_POST['vat_percent'] = 25;
        }

        if ($id = $account->save($_POST)) {
            return new k_SeeOther($this->url($id));
        }
        return $this->render();
    }

    function getValues()
    {
        return $this->body();
    }

    function getAccountGateway()
    {
        return $gateway = new Intraface_modules_accounting_AccountGateway($this->getYear());
    }

    function getAccount()
    {
    	return new Account($this->getYear());
    }

    function getAccounts()
    {
    	return $this->getAccountGateway()->findByType('stated', true);
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
}