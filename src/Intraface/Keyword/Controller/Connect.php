<?php
class Intraface_Keyword_Controller_Connect extends k_Component
{
    function getKernel()
    {
        return $this->context->getKernel();
    }

    function t($phrase)
    {
        return $phrase;
    }

    function renderHtml()
    {
        $this->getKernel()->useShared('keyword');

        if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
            $keyword = new Keyword($this->context->getObject(), $_GET['delete']);
            $keyword->delete();
        }

        $keyword = $this->context->getObject()->getKeywordAppender(); // starter objektet
        $keywords = $keyword->getAllKeywords(); // henter alle keywords
        $keyword_string = $keyword->getConnectedKeywordsAsString();

        // finder dem der er valgt
        $checked = array();
        foreach ($keyword->getConnectedKeywords() AS $key) {
            $checked[] = $key['id'];
        }
        $data = array(
        	'object' => $this->context->getObject(),
        	'keyword' => $keyword,
        	'keywords' => $keywords,
        	'checked' => $checked);

        $smarty = new k_Template(dirname(__FILE__) . '/../templates/connect.tpl.php');
        return $smarty->render($this, $data);
    }

    function postForm()
    {
        $this->getKernel()->useShared('keyword');
        $appender = $this->context->getObject()->getKeywordAppender(); // starter keyword objektet

        if (!$appender->deleteConnectedKeywords()) {
            $appender->error->set('Kunne ikke slette keywords.');
        }

        // strengen med keywords
        if (!empty($_POST['keywords'])) {
            $string_appender = new Intraface_Keyword_StringAppender($this->context->getObject()->getKeyword(), $appender);
            $string_appender->addKeywordsByString($_POST['keywords']);
        }

        // listen med keywords
        if (!empty($_POST['keyword']) AND is_array($_POST['keyword']) AND count($_POST['keyword']) > 0) {
            for ($i=0, $max = count($_POST['keyword']); $i < $max; $i++) {
                $appender->addKeyword(new Keyword($this->context->getObject(), $_POST['keyword'][$i]));
            }
        }

        if (!empty($_POST['close'])) {
            return new k_SeeOther($this->url('../../'));
        }
          if (!$appender->error->isError()) {
            //header('Location: connect.php?'.$id_name.'='.$this->context->getObject()->get('id'));
            //exit;
        }

        return $this->render();
    }
}