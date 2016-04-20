<?php
class Intraface_modules_debtor_Controller_Collection extends k_Component
{
    protected $error;
    protected $debtor;
    protected $template;
    protected $gateway;
    protected $doctrine;

    function __construct(k_TemplateFactory $template, Doctrine_Connection_Common $doctrine)
    {
        $this->template = $template;
        $this->doctrine = $doctrine;
    }

    function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_debtor_Controller_Show';
        } elseif ($name == 'create') {
            return 'Intraface_modules_debtor_Controller_Create';
        }
    }

    function renderHtml()
    {
        $debtor_module = $this->getKernel()->module('debtor');
        $contact_module = $this->getKernel()->useModule('contact');
        $product_module = $this->getKernel()->useModule('product');

        $this->getGateway()->setType($this->getType());

        if (intval($this->query("contact_id")) != 0) {
            $this->getGateway()->getDBQuery()->setFilter("contact_id", $this->query("contact_id"));
        }

        if (intval($this->query("product_id")) != 0) {
            $this->getGateway()->getDBQuery()->setFilter("product_id", $this->query("product_id"));
            if (isset($_GET['product_variation_id'])) {
                $this->getGateway()->getDBQuery()->setFilter("product_variation_id", $_GET["product_variation_id"]);
            }
        }

        // søgning
        if (isset($_GET["text"]) && $_GET["text"] != "") {
            $this->getGateway()->getDBQuery()->setFilter("text", $_GET["text"]);
        }
        if (isset($_GET["date_field"]) && $_GET["date_field"] != "") {
            $this->getGateway()->getDBQuery()->setFilter("date_field", $_GET["date_field"]);
        }

        if (isset($_GET["from_date"]) && $_GET["from_date"] != "") {
            $this->getGateway()->getDBQuery()->setFilter("from_date", $_GET["from_date"]);
        }

        if (isset($_GET["to_date"]) && $_GET["to_date"] != "") {
            $this->getGateway()->getDBQuery()->setFilter("to_date", $_GET["to_date"]);
        }

        if ($this->getGateway()->getDBQuery()->checkFilter("contact_id")) {
            $this->getGateway()->getDBQuery()->setFilter("status", "-1");
        } elseif (isset($_GET["status"]) && $_GET['status'] != '') {
            $this->getGateway()->getDBQuery()->setFilter("status", $_GET["status"]);
        } else {
            $this->getGateway()->getDBQuery()->setFilter("status", "-2");
        }

        if (!empty($_GET['not_stated']) and $_GET['not_stated'] == 'true') {
            $this->getGateway()->getDBQuery()->setFilter("not_stated", true);
        }

        if (isset($_GET['sorting']) && $_GET['sorting'] != 0) {
            $this->getGateway()->getDBQuery()->setFilter("sorting", $_GET['sorting']);
        }

        $this->getGateway()->getDBQuery()->usePaging("paging", $this->getKernel()->setting->get('user', 'rows_pr_page'));
        $this->getGateway()->getDBQuery()->storeResult("use_stored", $this->getType(), "toplevel");
        //$debtor->getDBQuery()->setExtraUri('&amp;type='.$debtor->get("type"));
        $this->getGateway()->getDBQuery()->setUri($this->url(null, array('use_stored' => 'true')));

        $data = array(
            'posts' => $this->getGateway()->findAll(),
            'debtor' => $this->getGateway());


        // @todo kan følgende ikke lige så godt hente fra $this->query()
        if (intval($this->getGateway()->getDBQuery()->getFilter('product_id')) != 0) {
            $data['product'] = new Product($this->getKernel(), $this->getGateway()->getDBQuery()->getFilter('product_id'));
            if (intval($this->getGateway()->getDBQuery()->getFilter('product_variation_id')) != 0) {
                $data['variation'] = $data['product']->getVariation($this->getGateway()->getDBQuery()->getFilter('product_variation_id'));
            }
        }
        if (intval($this->getGateway()->getDBQuery()->getFilter('contact_id')) != 0) {
            $data['contact'] = new Contact($this->getKernel(), $this->getGateway()->getDBQuery()->getFilter('contact_id'));
        }

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/collection');
        return $smarty->render($this, $data);
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
            $gateway = new Intraface_modules_currency_Currency_Gateway($this->doctrine);
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

    function renderXls()
    {
        if ($this->query('simple')) {
            Intraface_Doctrine_Intranet::singleton($this->getKernel()->intranet->getId());

            $debtor = Debtor::factory($this->getKernel(), intval($_GET["id"]), $this->getType());
            $dbquery = $debtor->getDbQuery();
            $type = $this->getType();
            unset($debtor);

            $dbquery->storeResult("use_stored", $type, "toplevel");
            $dbquery->loadStored();

            $gateway = new Intraface_modules_debtor_DebtorDoctrineGateway($this->doctrine, $this->getKernel()->user);
            // echo number_format(memory_get_usage())." After gateway initializd<br />"; die;
            $posts = $gateway->findByDbQuerySearch($dbquery);

            /*
            echo '<pre>';
            var_dump($posts->getFirst()->toArray()); die();
            print_r($posts->toArray(true));
            echo '</pre>';
            echo number_format(memory_get_usage())." After gateway initializd<br />"; die;
            */

            // spreadsheet
            $workbook = new Spreadsheet_Excel_Writer();
            $workbook->setVersion(8);

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
            $worksheet->setInputEncoding('UTF-8');

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
            $worksheet->write($i, 1, $status_types[$dbquery->getFilter('status')], $format_italic);
            $i++;

            $worksheet->write($i, 0, 'Søgetekst', $format_italic);
            $worksheet->write($i, 1, $dbquery->getFilter('text'), $format_italic);
            $i++;

            if ($dbquery->checkFilter('product_id')) {
                $product = new Product($this->getKernel(), $dbquery->getFilter('product_id'));

                $worksheet->write($i, 0, 'Produkt', $format_italic);
                $worksheet->write($i, 1, $product->get('name'), $format_italic);
                $i++;
            }

            if ($dbquery->checkFilter('contact_id')) {
                $contact = new Contact($this->getKernel(), $dbquery->getFilter('contact_id'));

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
            if ($type == 'invoice') {
                $worksheet->write($i, $c, 'Forfaldsbeløb', $format_bold);
                $c++;
            }
            $worksheet->write($i, $c, 'Kontaktnøgleord', $format_bold);
            $c++;

            if (!empty($product) && is_object($product) && get_class($product) == 'product') {
                $worksheet->write($i, $c, 'Antal valgte produkt', $format_bold);
                $c++;
            }

            $i++;

            $due_total = 0;
            $sent_total = 0;
            $total = 0;

            foreach ($posts as $debtor) {
                if (strtotime($debtor->getDueDate()->getAsIso()) < time() && ($debtor->getStatus() == "created" or $debtor->getStatus() == "sent")) {
                    $due_total += $debtor->getTotal()->getAsIso(2);
                }
                if ($debtor->getStatus() == "sent") {
                    $sent_total += $debtor->getTotal()->getAsIso(2);
                }
                    $total += $debtor->getTotal()->getAsIso(2);

                    /**
                     * @todo this could be done with Doctrine, but this seems only to have minimal memory usage
                     */
                    $contact = new Contact($this->getKernel(), $debtor->contact_id);
                    $worksheet->write($i, 0, $debtor->getNumber());
                    $worksheet->write($i, 1, $contact->get('number')); // $posts[$j]['contact']['number']
                    $worksheet->write($i, 2, $contact->get('name'));
                    $worksheet->write($i, 3, $debtor->getDescription());
                    $worksheet->writeNumber($i, 4, $debtor->getTotal()->getAsIso());
                    $worksheet->write($i, 5, $debtor->getDebtorDate()->getAsLocal('da_DK'));

                if ($debtor->getStatus() != "created") {
                    $worksheet->write($i, 6, $debtor->getDateSent()->getAsLocal('da_DK'));
                } else {
                    $worksheet->write($i, 6, "Nej");
                }

                if ($debtor->getStatus() == "executed" || $debtor->getStatus() == "canceled") {
                    $worksheet->write($i, 7, $this->t($debtor->getStatus()));
                } else {
                    $worksheet->write($i, 7, $debtor->getDueDate()->getAsLocal('da_DK'));
                }
                    $c = 8;
                if ($type == 'invoice') {
                    $worksheet->write($i, $c, '-'); // $posts[$j]['arrears']
                    $c++;
                }

                    /*
                    // not implemented
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
                    }*/

                    /*
                    // not implemented
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
                    }*/

                    $i++;
            }


            $i++;
            $i++;

            $worksheet->write($i, 0, 'Forfaldne', $format_italic);
            $worksheet->write($i, 1, number_format($due_total, 2, ",", "."), $format_italic);
            $i++;

            $worksheet->write($i, 0, 'Udestående (sendt):', $format_italic);
            $worksheet->write($i, 1, number_format($sent_total, 2, ",", "."), $format_italic);
            $i++;

            $worksheet->write($i, 0, 'Total:', $format_italic);
            $worksheet->write($i, 1, number_format($total, 2, ",", "."), $format_italic);
            $i++;

            // $worksheet->write($i, 0, number_format(memory_get_usage()));

            $worksheet->hideGridLines();

            return $workbook->close();
        }

        $debtor_module = $this->getKernel()->module('debtor');

        $this->getGateway()->getDBQuery()->storeResult("use_stored", $this->getType(), "toplevel");

        $posts = $this->getGateway()->findAll();

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
        $worksheet->write($i, 1, $status_types[$this->getGateway()->getDBQuery()->getFilter('status')], $format_italic);
        $i++;

        $worksheet->write($i, 0, 'Søgetekst', $format_italic);
        $worksheet->write($i, 1, $this->getGateway()->getDBQuery()->getFilter('text'), $format_italic);
        $i++;

        if ($this->getGateway()->getDbQuery()->checkFilter('product_id')) {
            $product = new Product($this->getKernel(), $this->getGateway()->getDbQuery()->getFilter('product_id'));

            $worksheet->write($i, 0, 'Produkt', $format_italic);
            $worksheet->write($i, 1, $product->get('name'), $format_italic);
            $i++;
        }

        if ($this->getGateway()->getDbQuery()->checkFilter('contact_id')) {
            $contact = new Contact($this->getKernel(), $this->getGateway()->getDbQuery()->getFilter('contact_id'));

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
        if ($this->getGateway()->getType() == 'invoice') {
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
        $type = $this->getType();
        unset($debtor);
        // HACK end //

        $i++;

        $due_total = 0;
        $sent_total = 0;
        $total = 0;

        foreach ($posts as $post) {
            if ($post["due_date"] < date("Y-m-d") && ($post["status"] == "created" or $post["status"] == "sent")) {
                $due_total += $post["total"];
            }
            if ($post["status"] == "sent") {
                $sent_total += $post["total"];
            }
            $total += $post["total"];

            $worksheet->write($i, 0, $post["number"]);
            $worksheet->write($i, 1, $post['contact']['number']);
            $worksheet->write($i, 2, $post["name"]);
            $worksheet->write($i, 3, $post["description"]);
            $worksheet->writeNumber($i, 4, $post["total"]);
            $worksheet->write($i, 5, $post["dk_this_date"]);

            if ($posts[$j]["status"] != "created") {
                $worksheet->write($i, 6, $post["dk_date_sent"]);
            } else {
                $worksheet->write($i, 6, "Nej");
            }

            if ($posts[$j]["status"] == "executed" || $post["status"] == "canceled") {
                $worksheet->write($i, 7, $this->t($post["status"], 'debtor'));
            } else {
                $worksheet->write($i, 7, $post["dk_due_date"]);
            }
            $c = 8;
            if ($type == 'invoice') {
                $worksheet->write($i, $c, $post['arrears']);
                $c++;
            }

            /*
            $keywords = array();
            $contact = new Contact($this->getKernel(), $post['contact']['id']);
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
            if (count($post['items']) > 0) {
                foreach ($post['items'] AS $item) {
                    if ($item['product_id'] == $product->get('id')) {
                        $quantity_product += $item['quantity'];
                    }
                }
            }
            $worksheet->write($i, $c, $quantity_product);
            $c++;
            }
            */

            $i++;
        }

        $i++;
        $i++;

        $worksheet->write($i, 0, 'Forfaldne', $format_italic);
        $worksheet->write($i, 1, number_format($due_total, 2, ",", "."), $format_italic);
        $i++;

        $worksheet->write($i, 0, 'Udestående (sendt):', $format_italic);
        $worksheet->write($i, 1, number_format($sent_total, 2, ",", "."), $format_italic);
        $i++;

        $worksheet->write($i, 0, 'Total:', $format_italic);
        $worksheet->write($i, 1, number_format($total, 2, ",", "."), $format_italic);
        $i++;

        $worksheet->hideGridLines();

        return $workbook->close();
    }

    function renderHtmlCreate()
    {
        return new k_SeeOther($this->url('create'));
    }

    function getReturnUrl($contact_id)
    {
        return $this->url(null, array('create', 'contact_id' => $contact_id));
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

    function getGateway()
    {
        Intraface_Doctrine_Intranet::singleton($this->getKernel()->intranet->getId());

        if (!empty($this->gateway)) {
            return $this->gateway;
        }

        $this->gateway = new Intraface_modules_debtor_DebtorGateway($this->getKernel());
        return $this->gateway;
    }

    /*
    function getDebtor()
    {
        if (is_object($this->debtor)) {
            return $this->debtor;
        }

        return ($this->debtor = Debtor::factory($this->getKernel(), $this->query('id'), $this->getType()));
    }
    */

    function getPosts()
    {
        return $this->getGateway()->findAll();
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
}
