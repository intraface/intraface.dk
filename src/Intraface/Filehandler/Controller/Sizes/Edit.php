<?php
class Intraface_Filehandler_Controller_Sizes_Edit extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function postForm()
    {
        $kernel = $this->getKernel();
        $shared_filehandler = $kernel->useShared('filehandler');
        $translation = $kernel->getTranslation('filehandler');

        $instance_manager = new Ilib_Filehandler_InstanceManager($kernel, (int)$this->POST['type_key']);

        if($instance_manager->save($this->body())) {
            return new k_SeeOther($this->context->url());
        }

        throw new Exception('An error occured when trying to save');
    }

    function renderHtml()
    {
        $kernel = $this->getKernel();
        $shared_filehandler = $kernel->useShared('filehandler');
        $translation = $kernel->getTranslation('filehandler');

        if (!empty($this->GET['type_key'])) {
            $instance_manager = new Ilib_Filehandler_InstanceManager($kernel, (int)$this->GET['type_key']);
            $value = $instance_manager->get();
        } else {
            $instance_manager = new Ilib_Filehandler_InstanceManager($kernel);
            $value = $instance_manager->get();
        }

        $this->document->setTitle('edit instance type');

        $data = array('instance_manager' => $instance_manager, 'value' => $value);

        $tpl = $this->template->create(dirname(__FILE__) . '/../../templates/sizes-edit');
        return $tpl->render($this, $data);
    }
}
