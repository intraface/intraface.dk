<?php
class Intraface_Filehandler_Controller_Sizes extends k_Component
{
    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getFilehandler()
    {
    	return new Ilib_Filehandler($this->getKernel());
    }

    function renderHtml()
    {
        $shared_filehandler = $this->getKernel()->useShared('filehandler');
        if (!empty($this->GET['delete_instance_type_key'])) {
            $instance_manager = new Ilib_Filehandler_InstanceManager($this->getKernel(), (int)$this->GET['delete_instance_type_key']);
            $instance_manager->delete();
        }

        $instance_manager = new Ilib_Filehandler_InstanceManager($this->getKernel());

        $this->document->setTitle('Filehandler settings');

        // $filehandler->createInstance();
        // $instances = $filehandler->instance->getTypes();

        $instances = $instance_manager->getList();

        $data = array('instance_manager' => $instance_manager, 'instances' => $instances);

        $tpl = new k_Template(dirname(__FILE__) . '/../templates/sizes.tpl.php');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
    	if ($this->POST['all_files']) {
            $manager = new Ilib_Filehandler_Manager($this->getKernel());
            $manager->deleteAllInstances();
    	}

        return new k_SeeOther($this->url());
    }

    function map($name)
    {
        if ($name == 'add') {
            return 'Intraface_Filehandler_Controller_Sizes_Edit';
        } elseif ($name == 'edit') {
            return 'Intraface_Filehandler_Controller_Sizes_Edit';
        }
    }
}
