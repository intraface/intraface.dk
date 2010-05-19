<?php
/**
 * Submit everybody to the newsletter for elevforeningen
 */
require('config.local.php');
require('common.php');

set_include_path(PATH_INCLUDE_PATH);


class ElevforeningenIntranet extends Intraface_Intranet
{
    private $intranet_id = 9;

    function __construct()
    {
        parent::__construct($this->intranet_id);
    }

    function hasModuleAccess()
    {
        return true;
    }
}

class ElevforeningenUser extends Intraface_User
{
    private $user_id = 2;

    function __construct()
    {
        parent::__construct($this->user_id);
    }

    function hasModuleAccess()
    {
        return true;
    }

}

$kernel = new Intraface_Kernel;
$kernel->intranet = new ElevforeningenIntranet;
$kernel->user = new ElevforeningenUser;
$kernel->setting = new Intraface_Setting($kernel->intranet->get('id'), $kernel->user->get('id'));
$kernel->useModule('contact');
$kernel->useModule('newsletter');

class ElevforeningenNewsletterList extends Newsletter
{
    public $kernel;
    public $list_id = 18;

    function __construct($kernel)
    {
        $this->kernel = $kernel;
        parent::__construct($kernel, $this->list_id);
    }

    function get() {
        return $this->list_id;
    }

}


$list = new ElevforeningenNewsletterList($kernel);
$subscriber = new NewsletterSubscriber($list);

$contact = new Contact($kernel);
$contact->getDBQuery()->setFilter('search', '');
$contacts = $contact->getList();
$i = 0;
foreach ($contacts as $contact) {
    if (empty($contact['email'])) continue;
    if ($subscriber->addContact(new Contact($kernel, $contact['id']))) $i++;
}
echo $i;


?>