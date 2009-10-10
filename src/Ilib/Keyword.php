<?php
class Ilib_Keyword
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $types = array();

    /**
     * @var array
     */
    protected $value = array();

    /**
     * Constructor
     *
     * @return void
     */
    function __construct($type, $extra_conditions = array(), $id = 0)
    {
        $this->id = (int)$id;
        $this->error = new Ilib_Error;
        $this->extra_conditions = $extra_conditions;
        $this->type = $type;
        // @todo before this is changed we need to change all the data in the database
        //$this->type = $this->getTypeKey($this->type);

        if ($this->id > 0) {
            $this->load();
        }
    }

    static function createFromKeyword($type, $extra_conditions = array(), $keyword)
    {
        $condition = $extra_conditions;
        $condition['type'] = $type;
        $condition['keyword'] = $keyword;
        $condition['active'] = 1;

        foreach ($condition as $column => $value) {
            $c[] = $column . " = '" . $value . "'";
        }

        $db = MDB2::singleton(DB_DSN);

        $result = $db->query("SELECT id FROM keyword WHERE "  .  implode(' AND ', $c));

        if (!PEAR::isError($result)) {
            if ($row = $result->fetchRow(MDB2_FETCHMODE_ASSOC))
            return new Ilib_Keyword($type, $extra_conditions, $row['id']);
        }

        throw new Exception('Error in query');
    }

    /**
     * Gets a type for a type key
     *
     * @param integer $key The key for a type
     *
     * @return string
     */
    function getType($key)
    {
        return $this->types[$key];
    }

    /**
     * Gets a type key
     *
     * @param string  $identifier Identifier for a type
     *
     * @return integer
     */
    function getTypeKey($identifier)
    {
        if (!$key = array_search($identifier, $this->types)) {
            throw new Exception('No type registered with this identifier ' . $identifier);
        }
        return $key;
    }

    /**
     * Register a type
     *
     * @param integer $key        The key for a type
     * @param string  $identifier Identifier for a type
     *
     * @return void
     */
    function registerType($key, $identifier)
    {
        $this->types[$key] = $identifier;
    }

    /**
     * Gets the keyword
     *
     * @return string
     */
    function getKeyword()
    {
        if (!isset($this->value['keyword'])) {
            return '';
        }
        return $this->value['keyword'];
    }

    /**
     * Gets the id for a keyword
     *
     * @return integer
     */
    function getId()
    {
        return $this->id;
    }

}
