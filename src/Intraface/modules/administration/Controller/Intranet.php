<?php
class Intraface_modules_administration_Controller_Intranet extends k_Component
{
    protected $intranetmaintenance;

    function renderHtml()
    {
        $smarty = new k_Template(dirname(__FILE__) . '/templates/intranet.tpl.php');
        return $smarty->render($this);
    }

    function renderHtmlEdit()
    {
        $modul = $this->getKernel()->module('administration');
        $shared_filehandler = $this->getKernel()->useShared('filehandler');
        $translation = $this->getKernel()->getTranslation('administration');

        if (isset($_GET['return_redirect_id'])) {
        	$redirect = Intraface_Redirect::factory($this->getKernel(), 'return');
        	$file_id = $redirect->getParameter('file_handler_id');

        	$intranet = new IntranetAdministration($this->getKernel());
        	$filehandler = new FileHandler($this->getKernel(), intval($file_id));
        	if ($filehandler->get('id') != 0) {

        		$type = $filehandler->get('file_type');
        		if ($type['mime_type'] == 'image/jpeg' || $type['mime_type'] == 'image/pjpeg') {
        			$values = $intranet->get();
        			$values['pdf_header_file_id'] = $filehandler->get('id');
        			$intranet->update($values);
        		} else {
        			$filehandler->error->set('Header should be a .jpg image - got '. $filehandler->get('file_type'));
        		}
        	}
        }
    	$intranet = $this->getIntranetMaintenance();
    	$values = $intranet->get();
    	$address = $intranet->address->get();

        $smarty = new k_Template(dirname(__FILE__) . '/templates/intranet-edit.tpl.php');
        return $smarty->render($this, array('intranet' => $intranet, 'kernel' => $this->getKernel()));
    }

    function getIntranetMaintenance()
    {
        if (is_object($this->intranetmaintenance)) {
            return $this->intranetmaintenance;
        }

        return $this->intranetmaintenance = new IntranetAdministration($this->getKernel());
    }

    function postMultipart()
    {
        $modul = $this->getKernel()->module('administration');
        $shared_filehandler = $this->getKernel()->useShared('filehandler');
      	$intranet = $this->getIntranetMaintenance();
       	$values = $_POST;

       	$filehandler = new FileHandler($this->getKernel());
       	$filehandler->createUpload();
       	if ($filehandler->upload->isUploadFile('new_pdf_header_file') && $id = $filehandler->upload->upload('new_pdf_header_file')) {
            $filehandler->load();

            $type = $filehandler->get('file_type');
            if ($type['mime_type'] == 'image/jpeg' || $type['mime_type'] == 'image/pjpeg') {
                $values['pdf_header_file_id'] = $id;
            } else {
                $intranet->error->set('Header should be a .jpg image - got ' . $type['mime_type']);
                $filehandler->delete();
            }
       	}

        if ($intranet->update($values)) {
            $address_values = $_POST;
            $address_values['name'] = $_POST['address_name'];
        	if ($intranet->address->validate($address_values) && $intranet->address->save($address_values)) {
        		if (isset($_POST['choose_file']) && $this->getKernel()->user->hasModuleAccess('filemanager')) {
        			$module_filemanager = $this->getKernel()->useModule('filemanager');
        			$redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
        			$url = $redirect->setDestination($module_filemanager->getPath().'select_file.php?images=1');
        			$redirect->askParameter('file_handler_id');
        			header('Location: ' . $url);
        			exit;
        		} else {
        			header('Location: '.$this->url('/main/controlpanel/intranet.php'));
        		}
                return new k_SeeOther($this->url());

        	}
            $values = $_POST;
            $address = $_POST;
        } else {
       		$values = $_POST;
       		$address = $_POST;
       	}

       	return $this->render();
    }

    function getKernel()
    {
    	return $this->context->getKernel();
    }

    function getValues()
    {
        $this->getKernel()->useShared('filehandler');

        $translation = $this->getKernel()->getTranslation('controlpanel');


        $values = $this->getKernel()->intranet->get();
        $address = $this->getKernel()->intranet->address->get();

        return array_merge($values, $address);
    }
}