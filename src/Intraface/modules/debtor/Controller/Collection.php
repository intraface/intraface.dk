<?php
class Intraface_modules_debtor_Controller_Collection extends k_Component
{
    protected $error;
    protected $debtor;
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_debtor_Controller_Show';
        } elseif ($name == 'create') {
            return 'Intraface_modules_debtor_Controller_Create';
        }
    }

    function getDebtor()
    {
        if (is_object($this->debtor)) {
            return $this->debtor;
        }

        return ($this->debtor = Debtor::factory($this->getKernel(), $this->query('id'), $this->getType()));
    }

    function getPosts()
    {
        return $this->getDebtor()->getList();
    }

    function renderXls()
    {
        $translation = $this->getKernel()->getTranslation('debtor');
        $debtor_module = $this->getKernel()->module('debtor');

        if (empty($_GET['id'])) $_GET['id'] = '';
        if (empty($_GET['type'])) $_GET['type'] = '';

        $debtor = Debtor::factory($this->getKernel(), intval($_GET["id"]), $this->context->getType());
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
        $worksheet = $workbook->addWorksheet(ucfirst($this->t($this->getType())));

        $i = 1;
        $worksheet->write($i, 0, $this->getKernel()->intranet->get('name'), $format_bold);
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
            $product = new Product($this->getKernel(), $debtor->getDbQuery()->getFilter('product_id'));

            $worksheet->write($i, 0, 'Produkt', $format_italic);
            $worksheet->write($i, 1, $product->get('name'), $format_italic);
            $i++;
        }

        if ($debtor->getDbQuery()->checkFilter('contact_id')) {
            $contact = new Contact($this->getKernel(), $debtor->getDbQuery()->getFilter('contact_id'));

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
                $contact = new Contact($this->getKernel(), $posts[$j]['contact']['id']);
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

        return $workbook->close();
    }

    function renderHtml()
    {
        Intraface_Doctrine_Intranet::singleton($this->getKernel()->intranet->getId());
        $mDebtor = $this->getKernel()->module('debtor');
        $contact_module = $this->getKernel()->useModule('contact');
        $product_module = $this->getKernel()->useModule('product');

        if (empty($_GET['id'])) $_GET['id'] = '';
        if (empty($_GET['type'])) $_GET['type'] = '';
        if (empty($_GET["contact_id"])) $_GET['contact_id'] = '';
        if (empty($_GET["status"])) $_GET['status'] = '';

        $debtor = Debtor::factory($this->getKernel(), intval($_GET["id"]), $this->context->getType());

        if (isset($_GET["action"]) && $_GET["action"] == "delete") {
            // $debtor = new CreditNote($this->getKernel(), (int)$_GET["delete"]);
            $debtor->delete();
        }

        if (isset($_GET["contact_id"]) && intval($_GET["contact_id"]) != 0) {
            $debtor->getDBQuery()->setFilter("contact_id", $_GET["contact_id"]);
        }

        if (isset($_GET["product_id"]) && intval($_GET["product_id"]) != 0) {
            $debtor->getDBQuery()->setFilter("product_id", $_GET["product_id"]);
            if (isset($_GET['product_variation_id'])) {
                $debtor->getDBQuery()->setFilter("product_variation_id", $_GET["product_variation_id"]);
            }
        }

        // s�gning
            // if (isset($_POST['submit'])
            if (isset($_GET["text"]) && $_GET["text"] != "") {
                $debtor->getDBQuery()->setFilter("text", $_GET["text"]);
            }

            if (isset($_GET["date_field"]) && $_GET["date_field"] != "") {
                $debtor->getDBQuery()->setFilter("date_field", $_GET["date_field"]);
            }

            if (isset($_GET["from_date"]) && $_GET["from_date"] != "") {
                $debtor->getDBQuery()->setFilter("from_date", $_GET["from_date"]);
            }

            if (isset($_GET["to_date"]) && $_GET["to_date"] != "") {
                $debtor->getDBQuery()->setFilter("to_date", $_GET["to_date"]);
            }

            if ($debtor->getDBQuery()->checkFilter("contact_id")) {
                $debtor->getDBQuery()->setFilter("status", "-1");
            } elseif (isset($_GET["status"]) && $_GET['status'] != '') {
                $debtor->getDBQuery()->setFilter("status", $_GET["status"]);
            } else {
                $debtor->getDBQuery()->setFilter("status", "-2");
            }

            if (!empty($_GET['not_stated']) AND $_GET['not_stated'] == 'true') {
                $debtor->getDBQuery()->setFilter("not_stated", true);
            }

        // er der ikke noget galt herunder (LO) - brude det ikke v�re order der bliver sat?
        if (isset($_GET['sorting']) && $_GET['sorting'] != 0) {
            $debtor->getDBQuery()->setFilter("sorting", $_GET['sorting']);
        }

        $debtor->getDBQuery()->usePaging("paging", $this->getKernel()->setting->get('user', 'rows_pr_page'));
        $debtor->getDBQuery()->storeResult("use_stored", $debtor->get("type"), "toplevel");
        $debtor->getDBQuery()->setExtraUri('&amp;type='.$debtor->get("type"));

        $data = array('posts' => $debtor->getList(), 'debtor' => $debtor);

        if (intval($debtor->getDBQuery()->getFilter('product_id')) != 0) {
            $data['product'] = new Product($this->getKernel(), $debtor->getDBQuery()->getFilter('product_id'));
            if (intval($debtor->getDBQuery()->getFilter('product_variation_id')) != 0) {
                $data['variation'] = $data['product']->getVariation($debtor->getDBQuery()->getFilter('product_variation_id'));

            }
        }

        if (intval($debtor->getDBQuery()->getFilter('contact_id')) != 0) {
            $contact = new Contact($this->getKernel(), $debtor->getDBQuery()->getFilter('contact_id'));
        }

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/collection');
        return $smarty->render($this, $data);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getError()
    {
        if (is_object($this->error)) {
            return $this->error;
        }
        return ($this->error = new Intraface_Error());
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
    	    return new k_SeeOther($this->url('../list/' . $debtor->get('id')));
    	}

    	return $this->render();
    }

    function getReturnUrl($contact_id)
    {
        return $this->url(null, array('create', 'contact_id' => $contact_id));
    }

    function renderHtmlCreate()
    {
        return new k_SeeOther($this->url('create'));
    }

    function getValues()
    {
        return array(
            'number' => ''
        );
    }

    function getAction()
    {
        return 'Create';
    }

    function getType()
    {
        return $this->context->getType();
    }

    function getContact()
    {
        $module = $this->getKernel()->module('contact');
        return new Contact($this->getKernel(), $this->query('contact_id'));
    }
}