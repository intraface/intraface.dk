<?php
class Intraface_modules_newsletter_NewsletterGateway
{
    protected $kernel;

    function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Creates a newsletter from a kernel and id
     *
     * @param integer $id     Newsletter id
     *
     * @return mixed
     */
    function findById($id)
    {
        $db = new DB_Sql;
        $db->query("SELECT list_id FROM newsletter_archieve WHERE intranet_id = ".$this->kernel->intranet->get('id')." AND active = 1 AND id = ".intval($id));
        if ($db->nextRecord()) {
            $list   = new NewsletterList($this->kernel, $db->f('list_id'));
            $letter = new Newsletter($list, $id);
            return $letter;
        }
        return false;
    }
}
