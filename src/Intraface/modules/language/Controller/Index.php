<?php
class Intraface_modules_language_Controller_Index extends k_Controller
{
    function GET()
    {
        $this->document->title = $this->__('Languages');

        $gateway = new Intraface_modules_language_Gateway;

        $languages = new Intraface_modules_language_Languages;
        $chosen = $languages->getChosenAsArray();

        $data = array('languages' => $gateway->getAll(), 'chosen' => $chosen);
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