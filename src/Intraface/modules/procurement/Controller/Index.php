<?php
class Intraface_modules_procurement_Controller_Index extends k_Component
{
    private $gateway;
    private $error;
    public $method = 'post';
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_procurement_Controller_Show';
        }
    }

    public function getKernel()
    {
        return $this->context->getKernel();
    }

    function getError()
    {
        if(!is_object($this->error)) {
            $this->error = new Intraface_Error();
        }

        return $this->error;
    }

    public function getProcurementGateway()
    {
        if(!is_object($this->gateway)) {
            $this->gateway = new Intraface_modules_procurement_ProcurementGateway($this->getKernel());
        }

        return $this->gateway;
    }

    function renderHtml()
    {
        // $this->document->title = $this->__('Procurement');

        $module = $this->getKernel()->module('procurement');
        $module = $this->getKernel()->useModule('contact');
        $translation = $this->getKernel()->getTranslation('procurement');

        $gateway = $this->getProcurementGateway();

        if (isset($_GET["contact_id"]) && intval($_GET["contact_id"]) != 0 && $this->getKernel()->user->hasModuleAccess('contact')) {
            # We need some way to identify this controller i used from contact? /Sune 29-11-2009
            $contact_module = $this->getKernel()->useModule('contact');
            $contact = new Contact($this->getKernel(), $_GET['contact_id']);
            $gateway->getDBQuery()->setFilter("contact_id", $_GET["contact_id"]);
        }

        if ($this->query("search") != '') {
            if ($this->query("text") != "") {
                $gateway->getDBQuery()->setFilter("text", $this->query("text"));
            }

            if ($this->query("from_date") != "") {
                $gateway->getDBQuery()->setFilter("from_date", $this->query("from_date"));
            }

            if ($this->query("to_date") != "") {
                $gateway->getDBQuery()->setFilter("to_date", $this->query("to_date"));
            }

            if ($this->query("status")) {
                $gateway->getDBQuery()->setFilter("status", $this->query("status"));
            }
        } else {
            if ($gateway->getDBQuery()->checkFilter("contact_id")) {
              $gateway->getDBQuery()->setFilter("status", "-1");
            } else {
                $gateway->getDBQuery()->setFilter("status", "-2");
            }
        }

        $gateway->getDBQuery()->usePaging("paging", $this->getKernel()->setting->get('user', 'rows_pr_page'));
        $gateway->getDBQuery()->storeResult("use_stored", "procurement", "toplevel");
        // $gateway->getDBQuery()->setExtraUri('&amp;type='.$gateway->get("type"));
        $procurements = $gateway->find();

        $data = array(
            'gateway' => $gateway,
            'procurements' => $procurements
        );

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/index');
        return $smarty->render($this, $data);
    }

    public function renderHtmlCreate()
    {
        $this->document->setTitle("Create procurement");
        $values["number"] = $this->getProcurementGateway()->getMaxNumber() + 1;

        $data = array(
            'values' => $values,
            'title' => 'Create procurement',
            'gateway' => $this->getProcurementGateway()
        );

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/procurement-edit');
        return $smarty->render($this, $data);
    }

    public function postForm()
    {
        $procurement = new Procurement($this->getKernel(), intval($_POST["id"]));

        if ($procurement->update($_POST)) {

            if (isset($_POST["recieved"]) && $_POST["recieved"] == "1") {
                $procurement->setStatus("recieved");
            }
            return new k_SeeOther($this->url($procurement->get("id")));
        } else {
            $values = $_POST;
            $title = "Ret indkøb";
        }

        return $this->render();
    }
}