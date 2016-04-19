<?php
class Intraface_modules_newsletter_ListGateway
{
    protected $kernel;

    function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    function findById($id)
    {
        return new NewsletterList($this->kernel, $id);
    }

    function findByContactId($contact_id)
    {
        $db = new DB_Sql;
        $db->query('SELECT * FROM newsletter_subscriber WHERE contact_id = ' . $contact_id);
        $lists = array();
        while ($db->nextRecord()) {
            $lists[$db->f('list_id')]['list'] = $this->findById($db->f('list_id'));
            $lists[$db->f('list_id')]['subscriber_id'] = $db->f('id');
        }
        return $lists;
    }
}
