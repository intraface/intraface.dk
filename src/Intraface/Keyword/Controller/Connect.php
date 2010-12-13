<?php
class Intraface_Keyword_Controller_Connect extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $this->document->setTitle('Add keywords');

        $this->getKernel()->useShared('keyword');

        $keyword = new Intraface_Keyword_Appender($this->context->getModel());
        $keywords = $keyword->getAllKeywords(); // henter alle keywords
        $keyword_string = $keyword->getConnectedKeywordsAsString();

        // finds chosen keywords
        $checked = array();
        foreach ($keyword->getConnectedKeywords() AS $key) {
            $checked[] = $key['id'];
        }
        $data = array(
        	'object' => $this->context->getModel(),
        	'keyword' => $keyword,
        	'keywords' => $keywords,
        	'checked' => $checked);

        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/connect');
        return $smarty->render($this, $data);
    }

    function postForm()
    {
        $this->getKernel()->useShared('keyword');
        $appender = new Intraface_Keyword_Appender($this->context->getModel());

        if (!$appender->deleteConnectedKeywords()) {
            $appender->error->set('Kunne ikke slette keywords.');
        }

        // string with keywords
        if ($this->body('keywords')) {
            $string_appender = new Intraface_Keyword_StringAppender(new Keyword($this->context->getModel()), $appender);
            $string_appender->addKeywordsByString($this->body('keywords'));
        }

        // list with keywordsd
        if ($this->body('keyword') AND is_array($this->body('keyword')) AND count($this->body('keyword')) > 0) {
            foreach ($this->body('keyword') as $k) {
                $keyword = new Keyword($this->context->getModel(), $k);
                $appender->addKeyword($keyword);
            }
        }

        if ($this->body('close')) {
            return new k_SeeOther($this->url('../../'));
        }
        if (!$appender->error->isError()) {
            return new k_SeeOther($this->url());
        }
        echo $appender->error->view();

        return $this->render();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}