<?php
class Intraface_modules_intranetmaintenance_Controller_Intranet_Show extends k_Component
{
    protected $template;
    protected $intranetmaintenance;
    public $method = 'put';
    public $error;
    protected $allowed_delete = array(
        1 => 'Bambus - VIP-betatest',
        21 => 'Bambus - Lars og Sune',
        22 => 'Bambus - betatest for alle brugere'
        );

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    protected function map($name)
    {
        if ($name == 'permission') {
            return 'Intraface_modules_intranetmaintenance_Controller_Intranet_Permission';
        }
    }

    function renderHtml()
    {
        $this->document->setTitle('Intranet');

        $modul = $this->getKernel()->module("intranetmaintenance");
        try {
            if ($this->getKernel()->user->hasModuleAccess('contact')) {
                $contact_module = $this->getKernel()->useModule('contact');
            }
        } catch (Exception $e) {
            $this->error = '<p>Kontaktmodulet findes ikke. Du har formentlig ikke registreret modulerne endnu. <a href="http://localhost/intraface/intraface/trunk/src/intraface.dk/modules/intranetmaintenance/modules.php?do=register">Registrer modulerne.</a></p>';
        }
        $translation = $this->getKernel()->getTranslation('intranetmaintenance');

        $intranet = new IntranetMaintenance($this->name());

        // add contact
        // @todo where to go?
        if (isset($_GET['add_contact']) && $_GET['add_contact'] == 1) {
            if ($this->getKernel()->user->hasModuleAccess('contact')) {
                $contact_module = $this->getKernel()->useModule('contact');

                $redirect = Intraface_Redirect::factory($kernel, 'go');
                $url = $redirect->setDestination($contact_module->getPath()."select_contact", NET_SCHEME . NET_HOST . $this->url());
                $redirect->askParameter('contact_id');
                $redirect->setIdentifier('contact');

                return new k_SeeOther($url);
            } else {
                throw new Exception("Du har ikke adgang til modulet contact");
            }
        }

        // add existing user
        if (isset($_GET['add_user']) && $_GET['add_user'] == 1) {
            $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
            $url = $redirect->setDestination(NET_SCHEME . NET_HOST . $this->url('../../user'), NET_SCHEME . NET_HOST . $this->url(null));
            $redirect->askParameter('user_id');
            $redirect->setIdentifier('add_user');
            return new k_SeeOther($url);
        }

        // return
        if (isset($_GET['return_redirect_id'])) {
            $redirect = Intraface_Redirect::factory($this->getKernel(), 'return');
            if ($redirect->get('identifier') == 'contact') {
                $intranet->setContact($redirect->getParameter('contact_id'));
            }
            if ($redirect->get('identifier') == 'add_user') {
                $user = new UserMaintenance($redirect->getParameter('user_id'));
                $user->setIntranetAccess($intranet->get('id'));
                $user_id = $user->get('id');
                return new k_SeeOther($this->url('../../user/' . $user->get('id'), array('intranet_id' => $this->name())));
            }
        }

        if (isset($_GET['delete_intranet_module_package_id']) && (int)$_GET['delete_intranet_module_package_id'] != 0) {

            $modulepackagemanager = new Intraface_modules_modulepackage_Manager($intranet);
            $modulepackagemanager->delete((int)$_GET['delete_intranet_module_package_id']);
        }

        $user = new UserMaintenance();
        $user->setIntranetId($intranet->get('id'));

        $gateway = new Intraface_ModuleGateway(MDB2::singleton(DB_DSN));
        $modules = $gateway->getList();

        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/intranet/show');
        return $smarty->render($this, array('modules' => $modules));
    }

    function renderHtmlEdit()
    {
        $this->document->setTitle('Edit intranet');

        $modul = $this->getKernel()->module("intranetmaintenance");
        $translation = $this->getKernel()->getTranslation('intranetmaintenance');

        $smarty = $this->template->render(dirname(__FILE__) . '/../templates/intranet/edit');
        return $smarty->render($this);
    }

    function renderHtmlDelete()
    {
        $smarty = $this->template->render(dirname(__FILE__) . '/../templates/intranet/delete');
        return $smarty->render($this);
    }

    function putForm()
    {
        $modul = $this->getKernel()->module("intranetmaintenance");
        $intranet = new IntranetMaintenance(intval($_POST["id"]));

    	$value = $_POST;
    	$address_value = $_POST;
    	$address_value["name"] = $_POST["address_name"];

    	if ($intranet->save($_POST) && $intranet->setMaintainedByUser($_POST['maintained_by_user_id'], $this->getKernel()->intranet->get('id'))) {
    		if ($intranet->address->save($address_value)) {
    			return new k_SeeOther($this->url(null));
    		}
    	}
    	return $this->render();
    }

    function DELETE()
    {

    	$db = new DB_Sql;
    	$db2 = new DB_Sql;

    	$intranet_id = intval($_POST['intranet_id']);

    	if (!array_key_exists($intranet_id, $allowed_delete)) {
    		throw new Exception('Du kan kun slette bambus beta og bambus - sune og lars');
    	}

    	$db->query("DELETE FROM accounting_account WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM accounting_post WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM accounting_vat_period WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM accounting_voucher WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM accounting_voucher_file WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM accounting_year WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM accounting_year_end WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM accounting_year_end_action WHERE intranet_id = " . $intranet_id);

    	// her skulle vi slette noget address

    	$db->query("DELETE FROM contact WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM contact_person WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM contact_message WHERE intranet_id  = " . $intranet_id);

    	$db->query("DELETE FROM cms_element WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM cms_page WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM cms_parameter WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM cms_section WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM cms_site WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM cms_template WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM cms_template_section WHERE intranet_id = " . $intranet_id);

    	$db->query("DELETE FROM comment WHERE intranet_id = " . $intranet_id);

    	$db->query("DELETE FROM debtor WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM debtor_item WHERE intranet_id = " . $intranet_id);

    	$db->query("DELETE FROM email WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM email_attachment WHERE intranet_id = " . $intranet_id);

    	$db->query("DELETE FROM file_handler WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM file_handler_instance WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM filehandler_append_file WHERE intranet_id = " . $intranet_id);

    	$db->query("DELETE FROM invoice_payment WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM invoice_reminder WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM invoice_reminder_item WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM invoice_reminder_unpaid_reminder WHERE intranet_id = " . $intranet_id);

    	$db->query("DELETE FROM keyword WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM keyword_x_object WHERE intranet_id = " . $intranet_id);

    	$db->query("DELETE FROM newsletter_archieve WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM newsletter_list WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM newsletter_subscriber WHERE intranet_id = " . $intranet_id);

    	$db->query("DELETE FROM onlinepayment WHERE intranet_id = " . $intranet_id);

    	$db->query("DELETE FROM procurement WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM procurement_item WHERE intranet_id = " . $intranet_id);

    	$db->query("DELETE FROM product WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM product_detail WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM product_related WHERE intranet_id = " . $intranet_id);

    	$db->query("DELETE FROM stock_adaptation WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM stock_regulation WHERE intranet_id = " . $intranet_id);

    	$db->query("DELETE FROM todo_list WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM todo_item WHERE intranet_id = " . $intranet_id);
    	$db->query("DELETE FROM todo_contact WHERE intranet_id = " . $intranet_id);

    	$this->removeDir('/home/intraface/upload/' . $intranet_id . '/');
    }

    protected function removeDir($path)
    {
		// Add trailing slash to $path if one is not there
		if (substr($path, -1, 1) != "/") {
			$path .= "/";
		}

		$normal_files = glob($path . "*");
		$hidden_files = glob($path . "\.?*");
		$all_files = array_merge($normal_files, $hidden_files);

		foreach ($all_files as $file) {
			# Skip pseudo links to current and parent dirs (./ and ../).
			if (preg_match("/(\.|\.\.)$/", $file)) {
               continue;
			}

			if (is_file($file) === TRUE) {
				// Remove each file in this Directory
				unlink($file);
				echo "Removed File: " . $file . "<br>";
			}
			else if (is_dir($file) === TRUE) {
				// If this Directory contains a Subdirectory, run this Function on it
				removeDir($file);
			}
		}
		// Remove Directory once Files have been removed (If Exists)
		if (is_dir($path) === TRUE) {
			rmdir($path);
			//echo "<br>Removed Directory: " . $path . "<br><br>";
		}
	}

    function getValues()
    {
        $intranet = new IntranetMaintenance($this->name());
        $value = $intranet->get();
        if (isset($intranet->address)) {
        	$address_value = $intranet->address->get();
        } else {
        	$address_value = array();
        }

        return array_merge($value, $address_value);
    }

    function getUsers()
    {
        $user = new UserMaintenance();
        $user->setIntranetId($this->getIntranet()->getId());
        return $user->getList($this->getKernel());
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getIntranet()
    {
        return new IntranetMaintenance($this->name());
    }

    /*
    function POST()
    {
        $modul = $this->getKernel()->module("intranetmaintenance");
        $intranet = new IntranetMaintenance(intval($this->name()));

        if (isset($_POST['add_module_package']) && $_POST['add_module_package'] != '') {
            $modulepackagemanager = new Intraface_modules_modulepackage_Manager($intranet);
            $modulepackagemanager->save($_POST['module_package_id'], $_POST['start_date'], $_POST['duration_month'].' month');
        }

        // Update permission
        if (isset($_POST["change_permission"])) {

            $modules = array();
            $modules = $_POST["module"];

            $intranet->flushAccess();

            // Hvis man er i det samme intranet som man redigere
            if ($this->getKernel()->intranet->get("id") == $intranet->get("id")) {
                // Finder det aktive modul
                $active_module = $this->getKernel()->getPrimaryModule();
                // Giver adgang til det
                $intranet->setModuleAccess($active_module->getId());
            }

            for ($i = 0, $max = count($modules); $i < $max; $i++) {
                $intranet->setModuleAccess($modules[$i]);
            }

            return new k_SeeOther($this->url());
        }
    }
    */
}


