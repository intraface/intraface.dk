<?php
class Intraface_modules_accounting_Controller_Voucher_Index extends k_Component
{
    protected $template;
    protected $db_sql;

    function __construct(k_TemplateFactory $template, DB_Sql $db_sql)
    {
        $this->template = $template;
        $this->db_sql = $db_sql;
    }

    protected function map($name)
    {
        if ($name == 'create') {
            return 'Intraface_modules_accounting_Controller_Voucher_Edit';
        } elseif (is_numeric($name)) {
            return 'Intraface_modules_accounting_Controller_Voucher_Show';
        } elseif ($name == 'popup') {
            return 'Intraface_modules_accounting_Controller_Voucher_Popup';
        }
    }

    function renderHtml()
    {
        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/voucher/index');
        return $smarty->render($this);
    }

    function renderXls()
    {
        $year = $this->getYear();

        $this->db_sql->query("SELECT * FROM accounting_voucher WHERE intranet_id = " . $year->kernel->intranet->get('id') . " AND year_id = " . $year->get('id') . " ORDER BY number ASC");
        //$i++;
        $posts = array();
        while ($this->db_sql->nextRecord()) {
            $voucher = new Voucher($year, $this->db_sql->f('id'));
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
        $worksheet->write($i, 0, $this->getKernel()->intranet->get('name'), $format_bold);

        $i = 2;

        $worksheet->write($i, 0, 'Dato', $format);
        $worksheet->write($i, 1, 'Bilagsnummer', $format);
        $worksheet->write($i, 2, 'Beskrivelse', $format);
        $worksheet->write($i, 3, 'Kontonummer', $format);
        $worksheet->write($i, 4, 'Konto', $format);
        $worksheet->write($i, 5, 'Debet', $format);
        $worksheet->write($i, 6, 'Kredit', $format);

        $i = 3;
        if (count($posts) > 0) {
            foreach ($posts as $post) {
                $worksheet->write($i, 0, $post['date_dk'], $format);
                $worksheet->write($i, 1, $post['voucher_number'], $format);
                $worksheet->write($i, 2, $post['text'], $format);
                $worksheet->write($i, 3, $post['account_number'], $format);
                $worksheet->write($i, 4, $post['account_name'], $format);
                $worksheet->write($i, 5, round($post['debet'], 2), $format);
                $worksheet->write($i, 6, round($post['credit'], 2), $format);
                $i++;
            }
        }
        $worksheet->hideGridLines();
        $workbook->close();

        /*
        $response = new k_HttpResponse(200, $workbook->close());
        $response->setContentType('application/vnd.ms-excel');
        return $response;
        */
    }

    function getPosts()
    {
        $year = new Year($this->getKernel());

        $voucher = new Voucher($year);
        return $posts = $voucher->getList();
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
