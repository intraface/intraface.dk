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
        if (is_numeric($name)) {
            return 'Intraface_Filehandler_Controller_Size';
        }
    }

    function renderHtml()
    {
        $shared_filehandler = $this->getKernel()->useModule('filemanager');

        $instance_manager = new Ilib_Filehandler_InstanceManager($this->getKernel());

        $this->document->setTitle('Filehandler settings');

        $instances = $instance_manager->getList();

        $data = array(
        	'instance_manager' => $instance_manager,
        	'instances' => $instances);

        $tpl = $this->template->create(dirname(__FILE__) . '/../templates/sizes');
        return $tpl->render($this, $data);
    }

    function renderHtmlAdd()
    {
        $kernel = $this->getKernel();
        $shared_filehandler = $kernel->useModule('filemanager');

        $instance_manager = new Ilib_Filehandler_InstanceManager($kernel);
        $value = $instance_manager->get();

        $this->document->setTitle('Add instance type');

        $data = array(
        	'instance_manager' => $instance_manager,
        	'value' => $value);

        $tpl = $this->template->create(dirname(__FILE__) . '/../templates/sizes-edit');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $shared_filehandler = $this->getKernel()->useModule('filemanager');

        $instance_manager = new Ilib_Filehandler_InstanceManager($this->getKernel(), (int)$this->body('type_key'));

        if ($id = $instance_manager->save($this->body())) {
            return new k_SeeOther($this->url());
        }

        return $this->render();
    }

    function DELETE()
    {
    	if ($this->body('all_files')) {
            $manager = new Ilib_Filehandler_Manager($this->getKernel());
            $manager->deleteAllInstances();
    	}

        return new k_SeeOther($this->url(null, array('flare' => 'Instances has been deleted')));
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
