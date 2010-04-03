<?php
class Intraface_modules_accounting_Controller_Post_Index extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    protected function map($name)
    {
        if (is_numeric($name)) {
        	return 'Intraface_modules_accounting_Controller_Post_Show';
        }
    }

    function renderHtml()
    {
        return '<h1>Poster</h1><p><a href="'.$this->url(null . '.xls').'">Excel</a></p>';
    }

    function renderHtmlCreate()
    {
        $post = new Post(new Voucher($this->getYear(), $this->context->name()));
        $values['date'] = $post->voucher->get('date_dk');

        $account = new Account($this->getYear());
        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/post/edit');
        return $smarty->render($this, array('post' => $post, 'account' => $account));
    }

    function postForm()
    {
        $year = $this->getYear();
        $this->getYear()->checkYear();

        // tjek om debet og credit account findes
        $post = new Post(new Voucher($this->getYear(), $this->context->name()));
        $account = Account::factory($this->getYear(), $_POST['account']);

        $date = new Intraface_Date($_POST['date']);
        $date->convert2db();

        $debet = new Intraface_Amount($_POST['debet']);
        if (!$debet->convert2db()) {
            $this->error->set('Beløbet kunne ikke konverteres');
        }
        $debet = $debet->get();

        $credit = new Intraface_Amount($_POST['credit']);
        if (!$credit->convert2db()) {
            $this->error->set('Beløbet kunne ikke konverteres');
        }
        $credit = $credit->get();

        if ($id = $post->save($date->get(), $account->get('id'), $_POST['text'], $debet, $credit)) {
            return new k_SeeOther($this->url('../'));
        } else {
            $values = $_POST;
        }
        return $this->render();
    }

    function renderXls()
    {
        $year = $this->getYear();
        $year->checkYear();

        $db = new DB_Sql;
        $db->query("SELECT * FROM accounting_voucher WHERE intranet_id = " . $year->kernel->intranet->get('id') . " AND year_id = " . $year->get('id') . " ORDER BY number ASC");
        //$i++;
        $posts = array();
        while ($db->nextRecord()) {
            $voucher = new Voucher($year, $db->f('id'));
            $posts = array_merge($voucher->getPosts(), $posts);
            //$i++;
        }

        $workbook = new Spreadsheet_Excel_Writer();
        $workbook->setVersion(8);

        // sending HTTP headers
        $workbook->send($this->getKernel()->intranet->get('name') . ' - poster ' . $year->get('label'));

        // Creating a worksheet
        $worksheet = $workbook->addWorksheet('Konti ' . $year->get('label'));
        $worksheet->setInputEncoding('UTF-8');

        $format_bold = $workbook->addFormat();
        $format_bold->setBold();
        $format_bold->setSize(8);

        $format_italic = $workbook->addFormat();
        $format_italic->setItalic();
        $format_italic->setSize(8);

        $format = $workbook->addFormat();
        $format->setSize(8);
        $i = 0;
        $worksheet->write($i, 0, $kernel->intranet->get('name'), $format_bold);

        $i = 2;

        $worksheet->write($i, 0, 'Dato', $format);
        $worksheet->write($i, 1, 'Bilagsnummer', $format);
        $worksheet->write($i, 2, 'Kontonummer', $format);
        $worksheet->write($i, 3, 'Konto', $format);
        $worksheet->write($i, 4, 'Debet', $format);
        $worksheet->write($i, 5, 'Kredit', $format);

        $i = 3;
            foreach ($posts AS $post) {
                $worksheet->write($i, 0, $post['date_dk'], $format);
                $worksheet->write($i, 1, $post['voucher_number'], $format);
                $worksheet->write($i, 2, $post['account_number'], $format);
                $worksheet->write($i, 3, $post['account_name'], $format);
                $worksheet->write($i, 4, round($post['debet'], 2), $format);
                $worksheet->write($i, 5, round($post['credit'], 2), $format);
                $i++;
            }

        $worksheet->hideGridLines();

        // Let's send the file
        $workbook->close();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getYear($id = 0)
    {
        return new Year($this->getKernel(), $id);
    }

    function getYearGateway()
    {
        $gateway = $this->context->getYearGateway();
        return $gateway;
    }
}