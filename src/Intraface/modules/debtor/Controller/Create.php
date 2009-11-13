<?php
class Intraface_modules_debtor_Controller_Create extends k_Component
{
    protected $debtor;

    function map($name)
    {
        if ($name == 'contact') {
            return 'Intraface_modules_contact_Controller_Choosecontact';
        }

        return parent::map($name);
    }

    function getDebtor()
    {
        if (is_object($this->debtor)) {
            return $this->debtor;
        }

        return $this->debtor = Debtor::factory($this->getKernel(), null, $this->getType());
    }

    function getPosts()
    {
        return $this->getDebtor()->getList();
    }

    function renderExcel()
    {
        if (empty($_GET['id'])) $_GET['id'] = '';
        if (empty($_GET['type'])) $_GET['type'] = '';

        $debtor = Debtor::factory($kernel, intval($_GET["id"]), $this->context->getType());
        $debtor->getDbQuery()->storeResult("use_stored", $debtor->get("type"), "toplevel");

        $posts = $debtor->getList();

        // spreadsheet
        $workbook = new Spreadsheet_Excel_Writer();

        $workbook->send('debtor.xls');

        $format_bold = $workbook->addFormat();
        $format_bold->setBold();
        $format_bold->setSize(8);

        $format_italic = $workbook->addFormat();
        $format_italic->setItalic();
        $format_italic->setSize(8);

        $format = $workbook->addFormat();
        $format->setSize(8);

        // Creating a worksheet
        $worksheet = $workbook->addWorksheet(ucfirst(__('title')));

        $i = 1;
        $worksheet->write($i, 0, $kernel->intranet->get('name'), $format_bold);
        $i++;

        $status_types = array(
            -3 => 'Afskrevet',
            -2 => 'Åbne',
            -1 => 'Alle',
            0 => 'Oprettet',
            1 => 'Sendt',
            2 => 'Afsluttet',
            3 => 'Annulleret');

        $worksheet->write($i, 0, 'Status', $format_italic);
        $worksheet->write($i, 1, $status_types[$debtor->getDbQuery()->getFilter('status')], $format_italic);
        $i++;

        $worksheet->write($i, 0, 'Søgetekst', $format_italic);
        $worksheet->write($i, 1, $debtor->getDbQuery()->getFilter('text'), $format_italic);
        $i++;

        if ($debtor->getDbQuery()->checkFilter('product_id')) {
            $product = new Product($kernel, $debtor->getDbQuery()->getFilter('product_id'));

            $worksheet->write($i, 0, 'Produkt', $format_italic);
            $worksheet->write($i, 1, $product->get('name'), $format_italic);
            $i++;
        }

        if ($debtor->getDbQuery()->checkFilter('contact_id')) {
            $contact = new Contact($kernel, $debtor->getDbQuery()->getFilter('contact_id'));

            $worksheet->write($i, 0, 'Kontakt', $format_italic);
            $worksheet->write($i, 1, $contact->address->get('name'), $format_italic);
            $i++;
        }

        $worksheet->write($i, 0, "Antal i søgningen", $format_italic);
        $worksheet->write($i, 1, count($posts), $format_italic);
        $i++;

        $i++;
        $worksheet->write($i, 0, 'Nummer', $format_bold);
        $worksheet->write($i, 1, 'Kontakt nummer', $format_bold);
        $worksheet->write($i, 2, 'Kontakt navn', $format_bold);
        $worksheet->write($i, 3, 'Beskrivelse', $format_bold);
        $worksheet->write($i, 4, 'Beløb', $format_bold);
        $worksheet->write($i, 5, 'Oprettet', $format_bold);
        $worksheet->write($i, 6, 'Sendt', $format_bold);
        //$worksheet->write($i, 7, __("due_date"), $format_bold);
        $c = 8;
        if ($debtor->get('type') == 'invoice') {
            $worksheet->write($i, $c, 'Forfaldsbeløb', $format_bold);
            $c++;
        }
        $worksheet->write($i, $c, 'Kontaktnøgleord', $format_bold);
        $c++;

        if (!empty($product) && is_object($product) && get_class($product) == 'product') {
            $worksheet->write($i, $c, 'Antal valgte produkt', $format_bold);
            $c++;
        }

        // HACK unsetting debtor which is actually ok to avoid memory problems //
        $type = $debtor->get('type');
        unset($debtor);
        // HACK end //

        $i++;

        $due_total = 0;
        $sent_total = 0;
        $total = 0;

        if (count($posts) > 0) {
            for ($j = 0, $max = count($posts); $j < $max; $j++) {

                if ($posts[$j]["due_date"] < date("Y-m-d") && ($posts[$j]["status"] == "created" OR $posts[$j]["status"] == "sent")) {
                    $due_total += $posts[$i]["total"];
                }
                if ($posts[$j]["status"] == "sent") {
                    $sent_total += $posts[$j]["total"];
                }
                $total += $posts[$j]["total"];

                $worksheet->write($i, 0, $posts[$j]["number"]);
                $worksheet->write($i, 1, $posts[$j]['contact']['number']);
                $worksheet->write($i, 2, $posts[$j]["name"]);
                $worksheet->write($i, 3, $posts[$j]["description"]);
                $worksheet->writeNumber($i, 4, $posts[$j]["total"]);
                $worksheet->write($i, 5, $posts[$j]["dk_this_date"]);

                if ($posts[$j]["status"] != "created") {
                    $worksheet->write($i, 6, $posts[$j]["dk_date_sent"]);
                } else {
                    $worksheet->write($i, 6, "Nej");
                }

                if ($posts[$j]["status"] == "executed" || $posts[$j]["status"] == "canceled") {
                    $worksheet->write($i, 7, __($posts[$j]["status"], 'debtor'));
                } else {
                    $worksheet->write($i, 7, $posts[$j]["dk_due_date"]);
                }
                $c = 8;
                if ($type == 'invoice') {
                    $worksheet->write($i, $c, $posts[$j]['arrears']);
                    $c++;
                }

                $keywords = array();
                $contact = new Contact($kernel, $posts[$j]['contact']['id']);
                $appender = $contact->getKeywordAppender();
                $keyword_ids = $appender->getConnectedKeywords();
                if (count($keyword_ids) > 0) {
                    foreach ($keyword_ids AS $keyword_id) {
                        $keyword = new Keyword($contact, $keyword_id);
                        $keywords[] = $keyword->getKeyword();
                    }
                    $worksheet->write($i, $c, implode(', ', $keywords));
                    $c++;
                }

                if (!empty($product) && is_object($product) && get_class($product) == 'product') {
                    $quantity_product = 0;
                    if (count($posts[$j]['items']) > 0) {
                        foreach ($posts[$j]['items'] AS $item) {
                            if ($item['product_id'] == $product->get('id')) {
                                $quantity_product += $item['quantity'];
                            }
                        }
                    }
                    $worksheet->write($i, $c, $quantity_product);
                    $c++;
                }

                $i++;

            }
        }


        $i++;
        $i++;

        $worksheet->write($i, 0, 'Forfaldne', $format_italic);
        $worksheet->write($i, 1, number_format($due_total, 2, ",","."), $format_italic);
        $i++;

        $worksheet->write($i, 0, 'Udestående (sendt):', $format_italic);
        $worksheet->write($i, 1, number_format($sent_total, 2, ",","."), $format_italic);
        $i++;

        $worksheet->write($i, 0, 'Total:', $format_italic);
        $worksheet->write($i, 1, number_format($total, 2, ",","."), $format_italic);
        $i++;


        $worksheet->hideGridLines();

        $workbook->close();

        exit;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function t($phrase)
    {
         return $phrase;
    }

    function postForm()
    {
    	$debtor = $this->getDebtor();
    	$contact = new Contact($this->getKernel(), $_POST["contact_id"]);

    	if (isset($_POST["contact_person_id"]) && $_POST["contact_person_id"] == "-1") {
    		$contact_person = new ContactPerson($contact);
    		$person["name"] = $_POST['contact_person_name'];
    		$person["email"] = $_POST['contact_person_email'];
    		$contact_person->save($person);
    		$contact_person->load();
    		$_POST["contact_person_id"] = $contact_person->get("id");
    	}

        if ($this->getKernel()->intranet->hasModuleAccess('currency') && !empty($_POST['currency_id'])) {
            $currency_module = $this->getKernel()->useModule('currency', false); // false = ignore user access
            $gateway = new Intraface_modules_currency_Currency_Gateway(Doctrine_Manager::connection(DB_DSN));
            $currency = $gateway->findById($_POST['currency_id']);
            if ($currency == false) {
                throw new Exception('Invalid currency');
            }

            $_POST['currency'] = $currency;
        }

    	if ($debtor->update($_POST)) {
    	    return new k_SeeOther($this->url('../' . $debtor->get('id')));
    	}

    	return $this->render();
    }

    function getReturnUrl($contact_id)
    {
        return $this->url(null, array('create', 'contact_id' => $contact_id));
    }

    function renderHtml()
    {
        if ($this->query('contact_id') == '') {
            return new k_SeeOther($this->url('contact'));
        }
        $smarty = new k_Template(dirname(__FILE__) . '/templates/edit.tpl.php');
        return $smarty->render($this);
    }

    function getValues()
    {
        return array(
            'number' => $this->getDebtor()->getMaxNumber() + 1,
            'dk_this_date' => date('d-m-Y'),
            'dk_due_date' => date('d-m-Y')
        );
    }

    function getAction()
    {
        return 'Create';
    }

    function getType()
    {
        return $this->context->context->getType();
    }

    function getContact()
    {
        $module = $this->getKernel()->module('contact');
        return new Contact($this->getKernel(), $this->query('contact_id'));
    }
}