<?php
class Intraface_modules_newsletter_Controller_Subscribers extends k_Component
{
    protected $registry;
    protected $subscriber;

    protected function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_newsletter_Controller_List';
        }
    }

    function getList()
    {
        return $this->context->getList();
    }

    function __construct(WireFactory $registry)
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
        $subscriber->getDBQuery()->setExtraUri('&amp;list_id='.$this->getList()->get('id'));
        $subscriber->getDBQuery()->storeResult("use_stored", 'newsletter_subscribers_'.$this->getList()->get("id"), "toplevel");
        return $subscriber->getList();
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module('newsletter');
        $translation = $this->getKernel()->getTranslation('newsletter');

        if (!$this->getKernel()->user->hasModuleAccess('contact')) {
            trigger_error("Du skal have adgang til kontakt-modullet for at se denne side");
        }

        $subscriber = $this->getSubscriber();

        if (isset($_GET['add_contact']) && $_GET['add_contact'] == 1) {
            if (!$this->getKernel()->user->hasModuleAccess('contact')) {
                throw new Exception('You do not have access to the contact module');
            }
            $contact_module = $this->getKernel()->useModule('contact');

            $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
            $url = $redirect->setDestination($contact_module->getPath()."select_contact.php", $module->getPath()."lists/".$this->getList()->get('id')."/subscribers");
            $redirect->askParameter('contact_id');
            $redirect->setIdentifier('contact');

            return new k_SeeOther($url);
        } elseif (isset($_GET['remind']) AND $_GET['remind'] == 'true') {
            $subscriber = new NewsletterSubscriber($this->getList(), intval($_GET['id']));
            if (!$subscriber->sendOptInEmail(Intraface_Mail::factory())) {
            	trigger_error('Could not send the optin e-mail');
            }
        } elseif (isset($_GET['optin'])) {
            $subscriber->getDBQuery()->setFilter('optin', intval($_GET['optin']));
        }

        if (isset($_GET['return_redirect_id'])) {
            $redirect = Intraface_Redirect::factory($this->getKernel(), 'return');
            if ($redirect->get('identifier') == 'contact') {
                $subscriber->addContact(new Contact($this->getKernel(), $redirect->getParameter('contact_id')));
            }

        }

        if (isset($_GET['delete']) AND intval($_GET['delete']) != 0) {
            $subscriber = new NewsletterSubscriber($this->getList(), $_GET['delete']);
            $subscriber->delete();
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