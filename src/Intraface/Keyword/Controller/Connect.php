<?php
class Intraface_Keyword_Controller_Connect extends k_Component
{
    function getKernel()
    {
        return $this->context->getKernel();
    }

    function renderHtml()
    {
        $this->getKernel()->useShared('keyword');

        if (!empty($_GET['delete']) AND is_numeric($_GET['delete'])) {
            $keyword = new Keyword($this->context->getModel(), $_GET['delete']);
            $keyword->delete();
        }

        $keyword = new Intraface_Keyword_Appender($this->context->getModel());
        $keywords = $keyword->getAllKeywords(); // henter alle keywords
        $keyword_string = $keyword->getConnectedKeywordsAsString();

        // finder dem der er valgt
        $checked = array();
        foreach ($keyword->getConnectedKeywords() AS $key) {
            $checked[] = $key['id'];
        }
        $data = array(
        	'object' => $this->context->getModel(),
        	'keyword' => $keyword,
        	'keywords' => $keywords,
        	'checked' => $checked);

        $smarty = new k_Template(dirname(__FILE__) . '/../templates/connect.tpl.php');
        return $smarty->render($this, $data);
    }

    function postForm()
    {
        $this->getKernel()->useShared('keyword');
        $appender = new Intraface_Keyword_Appender($this->context->getModel());

        if (!$appender->deleteConnectedKeywords()) {
            $appender->error->set('Kunne ikke slette keywords.');
        }

        // strengen med keywords
        if (!empty($_POST['keywords'])) {
            $string_appender = new Intraface_Keyword_StringAppender(new Keyword($this->context->getModel()), $appender);
            $string_appender->addKeywordsByString($_POST['keywords']);
        }

        // listen med keywords
        if (!empty($_POST['keyword']) AND is_array($_POST['keyword']) AND count($_POST['keyword']) > 0) {
            foreach ($_POST['keyword'] as $k) {
                $appender->addKeyword(new Keyword($this->context->getModel(), $k));
            }
        }

        if (!empty($_POST['close'])) {
            return new k_SeeOther($this->url('../../'));
        }
        if (!$appender->error->isError()) {
            return new k_SeeOther($this->url());
        }
        echo $appender->error->view();

        return $this->render();
    }
}