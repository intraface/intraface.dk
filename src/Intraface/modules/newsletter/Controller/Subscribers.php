<?php
class Intraface_modules_newsletter_Controller_Subscribers extends k_Component
{
    protected $subscriber;
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    protected function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_newsletter_Controller_Subscriber';
        } elseif ($name == 'addcontact') {
            return 'Intraface_modules_contact_Controller_Choosecontact';
        }
    }

    function getReturnUrl($contact_id)
    {
        return $this->url(null, array('contact_id' => $contact_id, 'add_contact' => 1));
    }

    function getList()
    {
        return $this->context->getList();
    }

    function getSubscriber()
    {
        if (is_object($this->subscriber)) {
            return $this->subscriber;
        }
        $list = $this->context->getList();
        return ($this->subscriber = new NewsletterSubscriber($this->getList()));
    }

    function getSubscribers()
    {
        $subscriber = $this->getSubscriber();
        $subscriber->getDBQuery()->useCharacter();
        $subscriber->getDBQuery()->defineCharacter('character', 'newsletter_subscriber.id');
        $subscriber->getDBQuery()->usePaging('paging');
        $subscriber->getDBQuery()->setUri($this->url());
        //$subscriber->getDBQuery()->setExtraUri('&amp;list_id='.$this->getList()->get('id'));
        $subscriber->getDBQuery()->storeResult("use_stored", 'newsletter_subscribers_'.$this->getList()->get("id"), "toplevel");
        return $subscriber->getList();
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module('newsletter');
        $translation = $this->getKernel()->getTranslation('newsletter');

        if (!$this->getKernel()->user->hasModuleAccess('contact')) {
            throw new Exception("Du skal have adgang til kontakt-modullet for at se denne side");
        }

        $subscriber = $this->getSubscriber();

        if (isset($_GET['add_contact']) && $_GET['add_contact'] == 1) {
            if (!$this->getKernel()->user->hasModuleAccess('contact')) {
                throw new Exception('You do not have access to the contact module');
            }
            $contact_module = $this->getKernel()->useModule('contact');

            /*
            $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
            $url = $redirect->setDestination($contact_module->getPath()."choosecontact", NET_SCHEME . NET_HOST . $this->url());
            $redirect->askParameter('contact_id');
            $redirect->setIdentifier('contact');



            */
            $subscriber->addContact(new Contact($this->getKernel(), $this->query('contact_id')));
            return new k_SeeOther($this->url(null, array('flare' => 'Contact has been added')));
        } elseif (isset($_GET['optin'])) {
            $subscriber->getDBQuery()->setFilter('optin', intval($_GET['optin']));
            $subscriber->getDBQuery()->setFilter('q', $_GET['q']);
        } /*elseif (isset($_GET['return_redirect_id'])) {
            $redirect = Intraface_Redirect::factory($this->getKernel(), 'return');
            if ($redirect->get('identifier') == 'contact') {
                $subscriber->addContact(new Contact($this->getKernel(), $redirect->getParameter('contact_id')));
            }

        }*/

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/subscribers');
        return $smarty->render($this);
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getLists()
    {
        $list = new NewsletterList($this->getKernel());
        return $list->getList();
    }
}