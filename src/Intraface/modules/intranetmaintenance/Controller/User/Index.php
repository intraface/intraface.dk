<?php
class Intraface_modules_intranetmaintenance_Controller_User_Index extends k_Component
{
    protected $user;
    public $method = 'post';
    protected $template;
    protected $redirect = null;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    protected function map($name)
    {
        if ($name == 'create') {
            return 'Intraface_modules_intranetmaintenance_Controller_User_Edit';
        } elseif (is_numeric($name)) {
            return 'Intraface_modules_intranetmaintenance_Controller_User_Show';
        }
    }

    function renderHtml()
    {
        $user = new UserMaintenance();

        if (isset($_GET['add_user_id']) && $_GET['add_user_id'] != 0) {
        	$this->getRedirect()->setParameter('user_id', intval($_GET['add_user_id']));
        	return new k_SeeOther($this->getRedirect()->getRedirect($this->url(null)));
        }

        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/user/index');
        return $smarty->render($this);
    }

    function renderHtmlCreate()
    {
        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/user/edit');
        return $smarty->render($this);
    }

    function postForm()
    {
        $user = $this->getUser();

        if (intval($this->body("intranet_id")) != 0) {
            $intranet = new Intraface_Intranet($this->body("intranet_id"));
            $intranet_id = $intranet->get("id");
            $address_value = $_POST;
            $address_value["name"] = $_POST["address_name"];
        } else {
            $intranet_id = 0;
            $address_value = array();
        }

        $value = $_POST;

        if ($user->update($_POST)) {
            if (isset($intranet)) {
                $user->setIntranetAccess($intranet->get('id'));
                $user->setIntranetId($intranet->get('id'));
                $user->getAddress()->save($address_value);
                if (is_numeric($this->context->name())) {
                    return new k_SeeOther($this->url('../', array('intranet_id' => $intranet->get("id"))));
                } else {
                    return new k_SeeOther($this->url($user->getId(), array('intranet_id' => $intranet->get("id"))));
                }
            } else {
                if (is_numeric($this->context->name())) {
                    return new k_SeeOther($this->url('../'));
                } else {
                    return new k_SeeOther($this->url($user->getId()));
                }

            }
        }
        return $this->render();
    }

    function getRedirect()
    {
        if (!is_null($this->redirect)) {
            return $this->redirect;
        }
         return $this->redirect = Intraface_Redirect::factory($this->getKernel(), 'receive');
    }

    function isAddUserTrue()
    {
        if ($this->getRedirect()->get('identifier') == 'add_user') {
        	return true;
        } else {
        	return false;
        }
    }

    function getUser()
    {
        if (is_object($this->user)) {
            return $this->user;
        }
        return $this->user = new UserMaintenance($this->context->name());
    }

    function getUsers()
    {
        if (isset($_GET["search"])) {

        	if (isset($_GET["text"]) && $_GET["text"] != "") {
        		$this->getUser()->getDBQuery($this->getKernel())->setFilter("text", $_GET["text"]);
        	}
        } elseif (isset($_GET['character'])) {
        	$this->getUser()->getDBQuery($this->getKernel())->useCharacter();
        }

        $this->getUser()->getDBQuery($this->getKernel())->defineCharacter('character', 'name');
        $this->getUser()->getDBQuery($this->getKernel())->usePaging("paging", $this->getKernel()->setting->get('user', 'rows_pr_page'));
        $this->getUser()->getDBQuery($this->getKernel())->storeResult("use_stored", "intranetmainenance_user", "sublevel");
        $this->getUser()->getDBQuery($this->getKernel())->setUri($this->url());

        return $this->getUser()->getList();
    }

    function getIntranetmaintenance()
    {
        if (is_object($this->intranetmaintenance)) {
            return $this->intranetmaintenance;
        }
        return $this->intranetmaintenance = new IntranetMaintenance($this->query('intranet_id'));
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
        if ($this->body()) {
            return $this->body();
        }
        $user = new UserMaintenance();
        $intranet_id = intval($_REQUEST["intranet_id"]);
        $value = array();
        $address_value = array();
        return array_merge($address_value, $value);
    }
}