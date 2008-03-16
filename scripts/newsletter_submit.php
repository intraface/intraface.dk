<?php
require('/home/intraface/intraface.dk/config.local.php');

require_once 'Intraface/Kernel.php';
require_once 'Intraface/Intranet.php';
require_once 'Intraface/User.php';
require_once 'Intraface/Setting.php';

error_reporting(E_ALL);

class ElevforeningenIntranet extends Intranet
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

class ElevforeningenUser extends User
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

$kernel = new Kernel;
$kernel->intranet = new ElevforeningenIntranet;
$kernel->user = new ElevforeningenUser;
$kernel->setting = new Setting($kernel->intranet->get('id'), $kernel->user->get('id'));
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
$contact->createDBQuery();
$contact->dbquery->setFilter('search', '');
$contacts = $contact->getList();
$i = 0;
foreach ($contacts as $contact) {
    if (empty($contact['email'])) continue;
    if ($subscriber->addContact(new Contact($kernel, $contact['id']))) $i++;
}
echo $i;


?>