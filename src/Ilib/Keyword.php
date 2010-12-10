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

    public $error;

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

    function getAllKeywords()
    {
        $keywords = array();

        $condition = $this->extra_conditions;
        $condition['keyword.type'] = $this->type;
        $condition['keyword.active'] = 1;
        foreach ($condition as $column => $value) {
            $c[] = $column . " = '" . $value . "'";
        }

        $db = new DB_Sql;
        $db->query("SELECT * FROM keyword
            WHERE " . implode(' AND ', $c) . "
            ORDER BY keyword ASC");

        $i = 0;
        while ($db->nextRecord()) {
            $keywords[$i]['id'] = $db->f('id');
            $keywords[$i]['keyword'] = $db->f('keyword');
            $i++;
        }

        return $keywords;
    }
}
