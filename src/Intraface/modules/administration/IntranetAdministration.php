<?php
/**
 *
 * @package Intraface_Administration
 * @author	Lars Olesen <lars@legestue.net>
 * @author	Sune Jensen <sj@sunet.dk>
 *
 * @since	 1.0
 * @version 1.0
 *
 * @todo VIGTIGT: Tilpasse til Kernel. Lige nu er $kernel bare smidt ind, fordi
 *       der var brug for den. Klasserne med IntranetAdmin er IKKE tilpasset ændringerne
 *       i $kernel.
 *
 * @todo Hvorfor ligger disse funktioner ikke bare i intranet. Det er jo ikke sådan,
 *       at man bare lige kan komme til dem?
 *
 */
class IntranetAdministration extends Intraface_Intranet
{
    protected $db; // databaseobject
    public $id; // intranet id
    public $address; // adresse object
    public $value; // array med oplysninger om intranettet
    public $kernel;
    public $error;

    function __construct($kernel)
    {
        $this->kernel = $kernel;
        $this->id = $kernel->intranet->get('id');
        $this->id = $this->load();
        $this->error = new Intraface_Error;
    }

    function update(array $input)
    {
        $input = safeToDb($input);
        settype($input['pdf_header_file_id'], 'integer');

        $validator = new Intraface_Validator($this->error);
        $validator->isString($input['name'], 'Navn skal være en streng', '', '');
        $validator->isString($input['identifier'], 'Identifier skal være en streng', '', '');
        $validator->isNumeric($input['pdf_header_file_id'], 'Header billede er ikke gyldigt', 'zero_or_greater');

        if (!$this->isIdentifierUnique($input['identifier'])) {
            $this->error->set('identifier has to be unique');
        }

        if ($this->error->isError()) {
            return 0;
        }

        $sql = "name = \"".$input['name']."\",
            identifier = \"".$input['identifier']."\",
            pdf_header_file_id = ".$input['pdf_header_file_id'];

        $db = new DB_sql;
        if ($this->id != 0) {
            $db->query("UPDATE intranet SET ".$sql.", date_changed=NOW() WHERE id = ".$this->id);
        }
        return $this->id;
    }

    function isIdentifierUnique($identifier)
    {
        $this->db = & MDB2::singleton(DB_DSN);
        $res =& $this->db->query("SELECT id FROM intranet WHERE identifier='".$this->db->escape($identifier, 'string')."' AND id != " . $this->db->escape($this->id, 'integer'));
        if ($res->numRows() > 0) {
            return false;
        }
        return true;
    }

    function isFilledIn()
    {
        if (empty($this->value['name']) || !isset($this->address) || !$this->address->get('address') || !$this->address->get('email')) return 0;
        return 1;
    }

}