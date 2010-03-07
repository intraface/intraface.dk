<?php
class Intraface_Filehandler_Controller_Sizes extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function map($name)
    {
        if ($name == 'add') {
            return 'Intraface_Filehandler_Controller_Sizes_Edit';
        } elseif ($name == 'edit') {
            return 'Intraface_Filehandler_Controller_Sizes_Edit';
        }
    }

    function renderHtml()
    {
        $shared_filehandler = $this->getKernel()->useShared('filehandler');
        if ($this->query('delete_instance_type_key')) {
            $instance_manager = new Ilib_Filehandler_InstanceManager($this->getKernel(), (int)$this->query('delete_instance_type_key'));
            $instance_manager->delete();
        }

        $instance_manager = new Ilib_Filehandler_InstanceManager($this->getKernel());

        $this->document->setTitle('Filehandler settings');

        // $filehandler->createInstance();
        // $instances = $filehandler->instance->getTypes();

        $instances = $instance_manager->getList();

        $data = array(
        	'instance_manager' => $instance_manager,
        	'instances' => $instances);

        $tpl = $this->template->create(dirname(__FILE__) . '/../templates/sizes');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
    	if ($this->body('all_files')) {
            $manager = new Ilib_Filehandler_Manager($this->getKernel());
            $manager->deleteAllInstances();
    	}

        return new k_SeeOther($this->url());
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getFilehandler()
    {
    	return new Ilib_Filehandler($this->getKernel());
    }
}
