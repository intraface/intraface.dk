<?php
class Intraface_Keyword_Controller_Connect extends k_Component
{
    public function getObject()
    {
        if (!method_exists($this->context, 'getObject')) {
        	throw new Exception('The context has to implement getObject()');
        }
    	return $this->context->getObject();
    }

    function t($phrase)
    {
        return $phrase;
    }

    function __($phrase)
    {
        return $phrase;
    }

    function renderHtml()
    {
        $kernel = $this->context->getKernel();
        $kernel->useShared('keyword');
        $translation = $kernel->getTranslation('keyword');

        $object = $this->getObject();

        if (!empty($this->GET['delete']) AND is_numeric($this->GET['delete'])) {
            $keyword = new Ilib_Keyword($object, $this->GET['delete']);
            $keyword->delete();
        }

        $keyword = $object->getKeyword();
        $appender = $object->getKeywordAppender(); // starter objektet
        $keywords = $keyword->getAllKeywords(); // henter alle keywords
        $keyword_string = $appender->getConnectedKeywordsAsString();

        // finder dem der er valgt
        $checked = array();
        foreach($appender->getConnectedKeywords() as $key) {
            $checked[] = $key['id'];
        }

        $data = array('object' => $object, 'keyword' => $keyword, 'keywords' => $keywords, 'checked' => $checked);
        $smarty = new k_Template(dirname(__FILE__) . '/../templates/connect.tpl.php');
        return $smarty->render($this, $data);
    }

    function POST()
    {
        $kernel = $this->context->getKernel();
        $kernel->useShared('keyword');
        $translation = $kernel->getTranslation('keyword');

        $object = $this->getObject();

        $keyword = $object->getKeywordAppender(); // starter keyword objektet

        if (!$keyword->deleteConnectedKeywords()) {
            $keyword->error->set('Kunne ikke slette keywords.');
        }

        // strengen med keywords
        if (!empty($_POST['keywords'])) {
            $appender = new Ilib_Keyword_StringAppender(new Ilib_Keyword($object), $keyword);
            $appender->addKeywordsByString($_POST['keywords']);
        }

        // listen med keywords
        if (!empty($_POST['keywords']) AND is_array($_POST['keywords']) AND count($_POST['keywords']) > 0) {
            for($i=0, $max = count($_POST['keywords']); $i < $max; $i++) {
                $keyword->addKeyword(new Ilib_Keyword($object, $this->POST['keyword'][$i]));
            }
        }

        if (!empty($_POST['close'])) {
            return new k_SeeOther($this->url('../../'));
        } else {
            return new k_SeeOther($this->url('./'));
        }
        return $this->render();
    }
}