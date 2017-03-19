<?php
class Intraface_modules_controlpanel_Controller_UserPreferences extends k_Component
{
    protected $error;
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    protected function map($name)
    {
        if ($name == 'intranet') {
            return 'Intraface_modules_intranetmaintenance_Controller_Intranet_Index';
        } elseif ($name == 'user') {
            return 'Intraface_modules_intranetmaintenance_Controller_User_Index';
        } elseif ($name == 'preferences') {
            return 'Intraface_modules_controlpanel_Controller_UserPreferences';
        }
    }

    function getValues()
    {
        /*
        $value['rows_pr_page'] = $this->getKernel()->setting->get('user', 'rows_pr_page');
        $value['theme'] = $this->getKernel()->setting->get('user', 'theme');
        $value['ptextsize'] = $this->getKernel()->setting->get('user', 'ptextsize');
        */
        $value['label'] = $this->getKernel()->setting->get('user', 'label');
        $value['language'] = $this->getKernel()->setting->get('user', 'language');
        //$value['htmleditor'] = $this->getKernel()->setting->get('user', 'htmleditor');
        return $value;
    }

    function renderHtml()
    {
        $smarty = $this->template->create(dirname(__FILE__) . '/templates/userpreferences');
        return $smarty->render($this);
    }

    function postForm()
    {
        /*
        if (!$this->getKernel()->setting->set('user', 'rows_pr_page', $_POST['rows_pr_page'])) {
            $error[] = 'rows_pr_page';
        }
        if (!$this->getKernel()->setting->set('user', 'theme', $_POST['theme'])) {
            $error[] = 'theme';
        }
        if (!$this->getKernel()->setting->set('user', 'ptextsize', $_POST['ptextsize'])) {
            $error[] = 'theme';
        }
        */


        if (isset($_POST['label']) and !isset($labels_standard[$_POST['label']])) {
            $this->getError()->set('error in label - not allowed');
        }

        if (!$this->getKernel()->setting->set('user', 'label', $_POST['label'])) {
            $this->getError()->set('error in label');
        }

        if (!empty($_POST['language']) and !array_key_exists($_POST['language'], $this->getKernel()->getTranslation()->getLangs())) {
            $this->getError()->set('error in language - not allowed');
        }

        if (!$this->getKernel()->setting->set('user', 'language', $_POST['language'])) {
            $this->getError()->set('error in language');
        }

        /*
        if ($this->getKernel()->user->hasModuleAccess('cms')) {
            $validator = new Intraface_Validator($error);
            $validator->isString($_POST['htmleditor'], 'error in htmleditor not a string', '');

            if (!array_key_exists($_POST['htmleditor'], $editors)) {
                $error->set('error in htmleditor not allowed');
            }
            if (!$this->getKernel()->setting->set('user', 'htmleditor', $_POST['htmleditor'])) {
                $error->set('error in htmleditor not saved');
            }
        }
        */

        if (!$this->getError()->isError()) {
            return new k_SeeOther($this->url('../'));
        }
        $value = $_POST;

        return $this->render();
    }

    function getLabelStandards()
    {
        return $labels_standard = array(
            0 => '3x7',
            1 => '2x8'
        );
    }

    function getError()
    {
        if (is_object($this->error)) {
            return $this->error;
        }
        return ($this->error = new Intraface_Error());
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getModules()
    {
        return $this->getKernel()->getModules();
    }
}
