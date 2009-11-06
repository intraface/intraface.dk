<?php
class Intraface_modules_newsletter_Controller_Subscribers extends k_Component
{
    protected $registry;
    protected $subscriber;

    protected function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_newsletter_Controller_Subscriber';
        }
    }

    function getList()
    {
        return $this->context->getList();
    }

    function __construct(k_Registry $registry)
    {
        $this->registry = $registry;
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

            $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
            $url = $redirect->setDestination($contact_module->getPath()."select_contact.php", NET_SCHEME . NET_HOST . $this->url());
            $redirect->askParameter('contact_id');
            $redirect->setIdentifier('contact');

            return new k_SeeOther($url);
        } elseif (isset($_GET['remind']) AND $_GET['remind'] == 'true') {
            $subscriber = new NewsletterSubscriber($this->getList(), intval($_GET['id']));
            if (!$subscriber->sendOptInEmail(Intraface_Mail::factory())) {
            	throw new Exception('Could not send the optin e-mail');
            }
        } elseif (isset($_GET['optin'])) {
            $subscriber->getDBQuery()->setFilter('optin', intval($_GET['optin']));
            $subscriber->getDBQuery()->setFilter('q', $_GET['q']);
        } elseif (isset($_GET['return_redirect_id'])) {
            $redirect = Intraface_Redirect::factory($this->getKernel(), 'return');
            if ($redirect->get('identifier') == 'contact') {
                $subscriber->addContact(new Contact($this->getKernel(), $redirect->getParameter('contact_id')));
            }

        }

        $smarty = new k_Template(dirname(__FILE__) . '/templates/subscribers.tpl.php');
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

    function t($phrase)
    {
         return $phrase;
    }
}