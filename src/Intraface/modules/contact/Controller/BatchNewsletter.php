<?php
class Intraface_modules_contact_Controller_BatchNewsletter extends k_Component
{
    protected $msg;
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        $this->getKernel()->useShared('email');
        $this->getKernel()->useModule('newsletter');

        $_GET['use_stored'] = true;

        $contact = new Contact($this->getKernel());
        $keyword = $contact->getKeywords();
        $keywords = $keyword->getAllKeywords();
        $contact->getDBQuery()->defineCharacter('character', 'address.name');
        $contact->getDBQuery()->storeResult('use_stored', 'contact', 'toplevel');
        $contacts = $contact->getList("use_address");

        $newsletter = new NewsletterList($this->getKernel());

        $data = array(
            'contacts' => $contacts,
            'newsletters' => $newsletter->getList(),
            'contact' => $contact
        );

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/batchnewsletter');
        return $smarty->render($this, $data);
    }

    function postForm()
    {
        $this->getKernel()->useModule('newsletter');

        $validator = new Intraface_Validator($this->getContact()->error);
        $validator->isNumeric($_POST['newsletter_id'], 'error in newsletter');

        $contact = new Contact($this->getKernel());
        $keyword = $contact->getKeywords();
        $keywords = $keyword->getAllKeywords();
        $contact->getDBQuery()->defineCharacter('character', 'address.name');
        $contact->getDBQuery()->storeResult('use_stored', 'contact', 'toplevel');
        $contacts = $contact->getList("use_address");

        if (!$contact->error->isError()) {
            // valideret subject og body
            $j = 0;

            foreach ($contacts as $c) {
                $contact = $this->context->getGateway()->findById($c['id']);

                $newsletter = new NewsletterList($this->getKernel(), $_POST['newsletter_id']);
                if ($newsletter->get('id') == 0) {
                    throw new Exception('Invalid newsletter list');
                }

                $subscriber = new NewsletterSubscriber($newsletter);
                $subscriber->addContact($contact);

                $j++;
            }
            $this->msg = 'I alt blev ' . $j . ' kontakter tilmeldt nyhedsbrevet. <a href="'.$this->url('../', array('use_stored' => 'true')).'">Tilbage til kontakter</a>.';
        } else {
            $value = $_POST;
        }

        return $this->render();
    }

    function getContact()
    {
        $this->getKernel()->useShared('email');

        $_GET['use_stored'] = true;

        return $contact = new Contact($this->getKernel());
    }

    function getContacts()
    {
        $this->getKernel()->useShared('email');

        $_GET['use_stored'] = true;

        $contact = new Contact($this->getKernel());
        $keyword = $contact->getKeywords();
        $keywords = $keyword->getAllKeywords();
        $contact->getDBQuery()->defineCharacter('character', 'address.name');
        $contact->getDBQuery()->storeResult('use_stored', 'contact', 'toplevel');
        $contacts = $contact->getList("use_address");
        return $contact;
    }

    function getMessage()
    {
        return $this->msg;
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }
}
