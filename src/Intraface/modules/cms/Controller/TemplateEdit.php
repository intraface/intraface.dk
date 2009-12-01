<?php
class Intraface_modules_cms_Controller_TemplateEdit extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        if (is_numeric($this->context->name())) {
            $template = CMS_Template::factory($this->getKernel(), 'id', $this->context->name());
            $value = $template->get();

        } else {
            $cmssite = new CMS_Site($this->getKernel(), $this->context->getSiteId());
            $template = new CMS_Template($cmssite);
            $value['site_id'] = $this->context->getSiteId();
            $value['for_page_type'] = 7; // all types;
        }

        $data = array(
            'template' => $template,
            'value' => $value,
            'translation' => $this->getKernel()->getTranslation()
        );

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/template-edit');
        return $tpl->render($this, $data);
    }

    function postForm()
    {

        // det kunne godt være, at der skulle laves noget så hvis det er første gang
        // man gemmer et template, så ryger man på template.php
        $module = $this->getKernel()->module('cms');
        $translation = $this->getKernel()->getTranslation('cms');

        $cmssite = new CMS_Site($this->getKernel(), $this->context->getSiteId());
        $template = new CMS_Template($cmssite, $_POST['id']);

        if ($id = $template->save($_POST)) {
            if (!empty($_POST['close'])) {
                return new k_SeeOther($this->url('../' . $id));
            } else {
                return new k_SeeOther($this->url('../' . $id));
            }
        } else {
            $value = $_POST;
            $value['for_page_type'] = array_sum($_POST['for_page_type']);
        }
        return $this->render();
    }


    function getKernel()
    {
        return $this->context->getKernel();
    }
}