Sample class

class Invoice {

 function __construct() {
 }

}

This class needs all of the above, and after reading the brilliant:

http://www.phppatterns.com/docs/design/the_registry

I am thinking about doing this:

$registry = Registry::singleton();
$registry->addEntry('user', new User);
$registry->addEntry('intranet', new Intranet);
$registry->addEntry('database', new MDB2($dsn));

And rewriting the invoice class:

class Invoice {

 private $id;
 private $database;
 private $user;
 private $intranet;
 private $value;

 public function __construct($id = 0) {
    $registry = Registry::singleton();
    $this->db = $registry->getEntry('database');
    $this->user = $registry->getEntry('user');
    $this->intranet = $registry->getEntry('intranet');
    $this->id = intval($id);

    if ($this->id > 0) {
       $this->load();
    }
 }

 function load() {
   if (!$this->user->hasPermission('view_invoice')) {
     trigger_error('user has no permission', E_USER_ERROR);
   }


    $result = $this->db->query("SELECT id, description
       FROM invoice
       WHERE
         id = " . $this->db->quote($this->id, 'integer') . "
         AND intranet_id = " .
           $this->db->quote($this->intranet->get('id'), 'integer') . "
         AND user_id = " .
           $this->db->quote($this->user->get('id'), 'integer') . ")";
    if ($row = $result->fetchRow()) {
      $this->value = $row;
    }
 }

 function get($key) {
   if(!empty($key)) {
     if(isset($this->value[$key])) {
       return($this->value[$key]);
     }
     else {
       return '';
     }
   }
   return $this->value;
 }

 function validate($array) {
    if (!Validate::string($array['description'])) {
      return 0;
    }
    return 1;
 }

 function save($array) {
   if (!$this->user->hasPermission('save_invoice')) {
     trigger_error('user has no permission', E_USER_ERROR);
   }

   if (!$this->validate($array)) {
      return 0;
   }

   if ($this->id == 0) {
      // insert into
      $next_id = $some_value;
      $sql = "INSERT (intranet_id, id, description) INTO invoice VALUES
         (
           ".$this->intranet->get('id').",
           ".$next_id.",
           ".$this->db->quote($array['description']).")";
   }
   else {
      $sql = "UPDATE invoice SET
           description = ".$this->db->quote($array['description'],
'text')."
           WHERE id = " . $this->db->quote($this->id, 'integer') . "
              AND intranet_id = " .
               $this->db->quote($this->intranet->get('id'), 'integer');
   }

   $result = $this->db->query($sql);
   if (PEAR::isError($result)) {
     trigger_error($result->getMessage, E_USER_ERROR);
   }
   return 1;
 }

}


Testing the class would be something like this:

Mock::generate('User');
Mock::generate('Intranet');
Mock::generate('MDB2');

class InvoiceTestCase extends UnitTestCase
{
    protected $user;
    protected $intranet;
    protected $mdb2;

    function setUp()
    {
        $this->user     = new MockUser();
        $this->intranet = new MockIntranet();
        $this->mdb2     = new MockMDB2();
        $registry = Registry::singleton();
        $registry->addEntry('user', $this->user);
        $registry->addEntry('intranet', $this->intranet);
        $registry->addEntry('database', $this->mdb2);
    }

    function testLoad()
    {
       $this->user->setReturnValue('hasPermission', false);
       $this->expectError('user has no permission');
       $invoice = new Invoice(1);
    }

    ...
}
?>
