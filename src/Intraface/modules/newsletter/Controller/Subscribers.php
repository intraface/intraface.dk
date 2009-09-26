<?php
class Intraface_modules_newsletter_Controller_Subscribers extends k_Component
{
    protected $registry;

    protected function map($name)
    {
        if (is_numeric($name)) {
            return 'Intraface_modules_newsletter_Controller_List';
        }
    }

    function __construct(WireFactory $registry)
    {
        $this->registry = $registry;
    }

    function renderHtml()
    {
        $module = $this->getKernel()->module('newsletter');
        $translation = $this->getKernel()->getTranslation('newsletter');

        if (!$this->getKernel()->user->hasModuleAccess('contact')) {
            trigger_error("Du skal have adgang til kontakt-modullet for at se denne side");
        }

        $list = $this->context->getList();
        $subscriber = new NewsletterSubscriber($list);


        if (isset($_GET['add_contact']) && $_GET['add_contact'] == 1) {
            if ($this->getKernel()->user->hasModuleAccess('contact')) {
                $contact_module = $this->getKernel()->useModule('contact');

                $redirect = Intraface_Redirect::factory($this->getKernel(), 'go');
                $url = $redirect->setDestination($contact_module->getPath()."select_contact.php", $module->getPath()."subscribers.php?list_id=".$list->get('id'));
                $redirect->askParameter('contact_id');
                $redirect->setIdentifier('contact');

                header("Location: ".$url);
                exit;
            } else {
                trigger_error("Du har ikke adgang til modulet contact", ERROR);
            }

        } elseif (isset($_GET['remind']) AND $_GET['remind'] == 'true') {
            $subscriber = new NewsletterSubscriber($list, intval($_GET['id']));
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
        //
        if (isset($_GET['delete']) AND intval($_GET['delete']) != 0) {

            $subscriber = new NewsletterSubscriber($list, $_GET['delete']);
            $subscriber->delete();
        }

        $subscriber->getDBQuery()->useCharacter();
        $subscriber->getDBQuery()->defineCharacter('character', 'newsletter_subscriber.id');
        $subscriber->getDBQuery()->usePaging('paging');
        $subscriber->getDBQuery()->setExtraUri('&amp;list_id='.$list->get('id'));
        $subscriber->getDBQuery()->storeResult("use_stored", 'newsletter_subscribers_'.$list->get("id"), "toplevel");
        $subscribers = $subscriber->getList();

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