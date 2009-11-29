<?php
/**
 * Extends Ilib_DBQuery class to customize it to Intraface
 *
 * @author Sune Jensen <sj@sunet.dk>
 */
class Intraface_DBQuery extends Ilib_DBQuery
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
        if (is_object($kernel->user) AND strtolower(get_class($kernel->user)) == 'intraface_user') {
            $this->setRowsPerPage($kernel->setting->get('user', 'rows_pr_page'));
        }
    }
}
