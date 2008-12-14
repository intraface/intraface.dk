<?php
class Intraface_modules_language_Controller_Index extends k_Controller
{
    function GET()
    {
        $this->document->title = $this->__('Languages');
        /*
        $this->document->options = array($this->url('create') => 'Create');

        $doctrine = $this->registry->get('doctrine');
        $languages = Doctrine::getTable('Intraface_modules_language_Language')->findByIntranetId($this->registry->get('kernel')->intranet->getId());
        if (count($languages) == 0) {
            return $this->render(dirname(__FILE__) . '/tpl/empty-table.tpl.php', array('message' => 'No languages has been created yet.'));
        }
        */
        $gateway = new Intraface_modules_language_Gateway;

        $languages = new Intraface_modules_language_Languages;
        $chosen = $languages->getChosen();

        $langs = array();
        foreach ($chosen as $lang) {
        	$langs[$lang->type_key] = $gateway->getByKey($lang->type_key);
        }

        $data = array('languages' => $gateway->getAll(), 'chosen' => $langs);
        return $this->render(dirname(__FILE__) . '/tpl/languages.tpl.php', $data);
    }

    function POST()
    {
        $languages = new Intraface_modules_language_Languages;
        $languages->flush();
        if (!empty($this->POST['language'])) {
        	foreach ($this->POST['language'] as $key) {
                $languages = new Intraface_modules_language_Languages;
                $languages->type_key = $key;
                $languages->save();
        	}
        }
        throw new k_http_Redirect($this->url());
    }

}