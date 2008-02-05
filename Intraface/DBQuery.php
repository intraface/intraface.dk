<?php
/**
 * Extends Ilib_DBQuery class to customize it to Intraface
 *
 * @author Sune Jensen <sj@sunet.dk>
 */
require_once 'DB/Sql.php';
require_once 'Ilib/DBQuery.php';

class DBQuery extends Ilib_DBQuery
{
    /**
     * Constructor
     *
     * @param object $kernel
     * @param string $table
     * @param string $required_conditions
     *
     * @return void
     */
    public function __construct($kernel, $table, $required_conditions = "")
    {

        parent::__construct($table, $required_conditions);
        $session_id = $kernel->getSessionId();
        $this->createStore(md5($session_id), 'intranet_id = '.$kernel->intranet->get('id'));
        if(strtolower(get_class($kernel->user)) == 'user') {
            $this->setRowsPerPage($kernel->setting->get('user', 'rows_pr_page'));
        }
    }
}
