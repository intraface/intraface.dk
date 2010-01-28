<?php
class Intraface_modules_intranetmaintenance_Controller_Intranet_Index extends k_Component
{
    protected $intranetmaintenance;
    public $method = 'post';
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    protected function map($name)
    {
        if ($name == 'create') {
            return 'Intraface_modules_intranetmaintenance_Controller_Intranet_Edit';
        } elseif (is_numeric($name)) {
            return 'Intraface_modules_intranetmaintenance_Controller_Intranet_Show';
        }
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module("intranetmaintenance");
        $translation = $this->getKernel()->getTranslation('intranetmaintenance');

        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/intranet/index');
        return $smarty->render($this);
    }

    function renderHtmlNew()
    {
        $this->document->setTitle('Create intranet');

        $modul = $this->getKernel()->module("intranetmaintenance");
        $translation = $this->getKernel()->getTranslation('intranetmaintenance');

        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/intranet/edit');
        return $smarty->render($this);
    }

    function postForm()
    {
        $modul = $this->getKernel()->module("intranetmaintenance");
        $translation = $this->getKernel()->getTranslation('intranetmaintenance');
        $intranet = new IntranetMaintenance();

    	$value = $_POST;
    	$address_value = $_POST;
    	$address_value["name"] = $_POST["address_name"];

    	if ($intranet->save($_POST) && $intranet->setMaintainedByUser($_POST['maintained_by_user_id'], $this->getKernel()->intranet->get('id'))) {
    		if ($intranet->address->save($address_value)) {
    			return new k_SeeOther($this->url($intranet->getId()));
    		}
    	}
    	return $this->render();
    }

    function getIntranets()
    {
        if (isset($_GET["search"])) {
            if (isset($_GET["text"]) && $_GET["text"] != "") {
                $this->getIntranetmaintenance()->getDBQuery($this->getKernel())->setFilter("text", $_GET["text"]);
            }
        } elseif (isset($_GET['character'])) {
            $this->getIntranetmaintenance()->getDBQuery($this->getKernel())->useCharacter();
        }

        $this->getIntranetmaintenance()->getDBQuery($this->getKernel())->defineCharacter('character', 'name');
        $this->getIntranetmaintenance()->getDBQuery($this->getKernel())->usePaging("paging", $this->getKernel()->setting->get('user', 'rows_pr_page'));
        $this->getIntranetmaintenance()->getDBQuery($this->getKernel())->storeResult("use_stored", "intranetmainenance_intranet", "toplevel");
        $this->getIntranetmaintenance()->getDBQuery()->setUri($this->url());
        return $this->getIntranetmaintenance()->getList();
    }

    function getIntranetmaintenance()
    {
        if (is_object($this->intranetmaintenance)) {
            return $this->intranetmaintenance;
        }
        return $this->intranetmaintenance = new IntranetMaintenance();
    }

    function getKernel()
    {
    	return $this->context->getKernel();
    }

    function getIntranet()
    {
        return $this->getKernel()->intranet;
    }

    function getValues()
    {
   		$intranet = new IntranetMaintenance();
   		$value = array();
   		$address_value = array();

        $array = array_merge($value, $address_value);
        return $array;
    }
}
