<?php
class Intraface_modules_newsletter_SubscribersGateway
{
    protected $dbquery;

    function getDBQuery()
    {
        return $this->dbquery;
    }

    function getAllUnsubscribersForList($list)
    {
        $this->dbquery = new Intraface_DBQuery($list->kernel, "newsletter_subscriber", "newsletter_subscriber.list_id=". $list->get('id') . " AND newsletter_subscriber.intranet_id = " . $list->kernel->intranet->get('id'));
        $this->dbquery->setJoin("LEFT", "contact", "newsletter_subscriber.contact_id = contact.id AND contact.intranet_id = ".$list->kernel->intranet->get("id"), '');
        $this->dbquery->setJoin("LEFT", "address", "address.belong_to_id = contact.id AND address.active = 1 AND address.type = 3", '');
        $this->dbquery->setFilter('optin', 1);
        $this->dbquery->setFilter('active', 0);
        $this->dbquery->setSorting('date_submitted DESC');
        $this->dbquery->setCondition('newsletter_subscriber.optin = '.$this->getDBQuery()->getFilter('optin'));
        $this->dbquery->setCondition('newsletter_subscriber.active = '.$this->getDBQuery()->getFilter('active'));
        $this->dbquery->setSorting("newsletter_subscriber.date_unsubscribe DESC");
        return $this->getDBQuery()->getRecordset("newsletter_subscriber.id, date_unsubscribe, contact_id, address.name, DATE_FORMAT(date_unsubscribe, '%d-%m-%Y %H-%i-%s') AS dk_date_unsubscribe", "", false);
    }

    function getByContactId($list, $id)
    {
        $this->dbquery = new Intraface_DBQuery($list->kernel, "newsletter_subscriber", "newsletter_subscriber.list_id=". $list->get('id') . " AND newsletter_subscriber.intranet_id = " . $list->kernel->intranet->get('id'));
        $this->dbquery->setFilter('contact_id', $id);
        $this->getDBQuery()->setCondition('newsletter_subscriber.contact_id = '.$this->getDBQuery()->getFilter('contact_id'));

        return $this->getDBQuery()->getRecordset("id, date_unsubscribe, contact_id, DATE_FORMAT(date_unsubscribe, '%d-%m-%Y %H-%i-%s') AS dk_date_unsubscribe", "", false);
    }

    function setNewContactId($old_id, $new_id)
    {
        // @todo - make sure to delete old ones
        $db = MDB2::singleton();
        $db->query('UPDATE newsletter_subscriber SET contact_id = ' . $new_id . ' WHERE contact_id = ' . $old_id);
    }
}
