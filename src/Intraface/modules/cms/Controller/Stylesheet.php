<?php
class Intraface_modules_cms_Controller_Stylesheet extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module('cms');
        $translation = $this->getKernel()->getTranslation('cms');
        $cmssite = new CMS_Site($this->getKernel(), $_GET['site_id']);
        $value['site_id'] = $cmssite->get('id');
        $value['css'] = $cmssite->stylesheet->get('css_own');

        $tpl = $this->template->create(dirname(__FILE__) . '/templates/stylesheet-edit');
        return $tpl->render($this);
        return new k_SeeOther($this->url('../../../../modules/cms/'));
    }

    function postForm()
    {
        $module = $this->getKernel()->module('cms');
        $translation = $this->getKernel()->getTranslation('cms');

        $cmssite = new CMS_Site($this->getKernel(), $_POST['site_id']);
        if ($cmssite->stylesheet->save($_POST)) {
            if (!empty($_POST['close'])) {
                header('Location: index.php?id='.$cmssite->get('id'));
                exit;
            } else {
                header('Location: stylesheet_edit.php?site_id='.$cmssite->get('id'));
                exit;
            }
        } else {
            $value = $_POST;
        }
        return $this->render();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
