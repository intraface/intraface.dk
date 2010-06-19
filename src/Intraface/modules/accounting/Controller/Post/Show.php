<?php
class Intraface_modules_accounting_Controller_Post_Show extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function dispatch()
    {
        if ($this->getPost()->get('id') == 0) {
            throw new k_PageNotFound();
        }
        return parent::dispatch();
    }

    function renderHtml()
    {
        return 'Left blank intentionally';
        /*
        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/post/show');
        return $smarty->render($this);
        */
    }

    function renderHtmlEdit()
    {
        $post = $this->getPost();
        $values = $post->get();
        $values['date'] = $post->get('date_dk');
        $values['debet'] = $post->get('debet');
        $values['credit'] = $post->get('credit');
        $account = new Account($this->getYear());
        $smarty = $this->template->create(dirname(__FILE__) . '/../templates/post/edit');
        return $smarty->render($this, array('post' => $post, 'account' => $account));
    }

    function renderHtmlDelete()
    {
        $post = $this->getPost();
        $post->delete();
        return new k_SeeOther($this->url('../../'));
    }

    function postForm()
    {
        $year = $this->getYear();

        // tjek om debet og credit account findes
        $post = $this->getPost();
        $account = Account::factory($this->getYear(), $_POST['account']);

        $date = new Intraface_Date($_POST['date']);
        $date->convert2db();

        $debet = new Intraface_Amount($_POST['debet']);
        if (!$debet->convert2db()) {
            $this->error->set('Beløbet kunne ikke konverteres');
        }
        $debet = $debet->get();

        $credit = new Intraface_Amount($_POST['credit']);
        if (!$credit->convert2db()) {
            $this->error->set('Beløbet kunne ikke konverteres');
        }
        $credit = $credit->get();

        if ($id = $post->save($date->get(), $account->get('id'), $_POST['text'], $debet, $credit)) {
            return new k_SeeOther($this->url('../../'));
        } else {
            $values = $_POST;
        }
        return $this->render();
    }

    function getPost()
    {
        return $post = Post::factory($this->getYear(), (int)$this->name());
    }

    function getYear()
    {
        return $this->context->getYear();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}