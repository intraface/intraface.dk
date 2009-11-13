<?php
/**
 * Merges a contact with other contacts
 *
 * To merge a contact we need to change the contact id of the following:
 *
 * - debtor
 * - newsletter
 * - procurement
 *
 * All the main classes of the modules need to implement a method called setContact().
 *
 * When the contact has been merged, the contact has to be deleted. Make sure that you
 * are asked a couple of times about what to do.
 *
 */
class Intraface_modules_contact_Controller_Merge extends k_Component
{
    function renderHtml()
    {
        if ($this->getKernel()->user->hasModuleAccess('debtor')) {
            $invoice_module = $this->getKernel()->useModule('debtor');
        }

        $contact = new Contact($this->getKernel(), $this->context->name());
        $similar_contacts = $contact->getSimilarContacts();

        $smarty = new k_Template(dirname(__FILE__) . '/templates/merge.tpl.php');
        return $smarty->render($this);
    }

    function getSimilarContacts()
    {
        $contact = new Contact($this->getKernel(), $this->context->name());
        return $contact->getSimilarContacts();
    }

    function getKernel()
    {
        return $this->context->getKernel();
    }

    function getContact()
    {
        return $contact = new Contact($this->getKernel(), intval($this->context->name()));
    }

    function t($phrase)
    {
         return $phrase;
    }

    function getGateways()
    {
        if ($this->getKernel()->user->hasModuleAccess('debtor')) {
            $invoice_module = $this->getKernel()->useModule('debtor');
        }

        return $gateways = array(
            'newsletter' => new Intraface_modules_newsletter_SubscribersGateway(),
        	'procurement' => new Intraface_modules_procurement_ProcurementGateway(),
        );
    }

    function postForm()
    {
        $gateways = $this->getGateways();

        $new_contact = new Contact($this->getKernel(), intval($this->context->name()));
        $chosen_contacts = $this->body('contact');

        foreach ($chosen_contacts as $c) {
            $old_contact = new Contact($this->getKernel(), $c);
            foreach ($gateways as $gateway) {
                $gateway->setNewContactId($old_contact->getId(), $new_contact->getId());
            }
        }
        // @todo what to do with the contact?

        return new k_SeeOther($this->url(null));
    }
}