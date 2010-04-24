<?php
class Intraface_Filehandler_Controller_Size extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $shared_filehandler = $this->getKernel()->useModule('filemanager');

        $instance_manager = new Ilib_Filehandler_InstanceManager($this->getKernel(), (int)$this->name());
        $value = $instance_manager->get();

        $this->document->setTitle('Edit instance type');

        $data = array(
        	'instance_manager' => $instance_manager,
        	'value' => $value);

        $tpl = $this->template->create(dirname(__FILE__) . '/../templates/sizes-edit');
        return $tpl->render($this, $data);
    }

    function postForm()
    {
        $shared_filehandler = $this->getKernel()->useModule('filemanager');

        $instance_manager = new Ilib_Filehandler_InstanceManager($this->getKernel(), (int)$this->name());

        if ($instance_manager->save($this->body())) {
            return new k_SeeOther($this->context->url());
        }

        return $this->render();
    }

    function renderHtmlDelete()
    {
        $instance_manager = new Ilib_Filehandler_InstanceManager($this->getKernel(), (int)$this->name());
        $instance_manager->delete();
        return new k_SeeOther($this->url('../'));
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
