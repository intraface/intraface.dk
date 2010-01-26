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
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        if ($this->getKernel()->user->hasModuleAccess('debtor')) {
            $invoice_module = $this->getKernel()->useModule('debtor');
        }

        $contact = new Contact($this->getKernel(), $this->context->name());
        $similar_contacts = $contact->getSimilarContacts();

        $smarty = $this->template->create(dirname(__FILE__) . '/templates/merge');
        return $smarty->render($this);
    }

    function postForm()
    {
        $new_contact = $this->getContact();
        $chosen_contacts = $this->body('contact');

        foreach ($chosen_contacts as $c) {
            $old_contact = new Contact($this->getKernel(), $c);
            foreach ($this->context->getDependencies() as $dependency) {
                $dependency['gateway']->setNewContactId($old_contact->getId(), $new_contact->getId());
            }

            $this->setNewContactId($old_contact->getId(), $new_contact->getId());

            if ($this->body('delete_merged_contacts') == 'yes') {
                $old_contact->delete();
            }
        }

        if (!$this->context->getContact()->hasSimilarContacts()) {
            return new k_SeeOther($this->url('../'));
        }

        return new k_SeeOther($this->url(null));
    }

    /**
     * @todo --> maybe this should just be used on merge?
     *
     * @param $old_contact_id
     * @param $new_contact_id
     *
     * @return boolean
     */
    function setNewContactId($old_contact_id, $new_contact_id)
    {
        $db = new DB_Sql;
        $db->query('UPDATE contact_person SET contact_id = ' . $new_contact_id . ' WHERE contact_id = ' . $old_contact_id);
        return true;
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
}