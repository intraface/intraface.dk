<?php
require 'pdoext/connection.php';
require 'pdoext/tablegateway.php';

class Intraface_TableGateway extends pdoext_TableGateway
{
    private $user;

    function __construct($table, $conn, $user)
    {
        $this->user = $user;
        parent::__construct($table, $conn);
    }

    function insert($data)
    {
        $data['intranet_id'] = $this->user->getActiveIntranetId();
        $data['user_id'] = $this->user->getId();
        parent::insert($data);
    }

    function update($data, $condition)
    {
        $condition['intranet_id'] = $this->user->getActiveIntranetId();
        $condition['user_id'] = $this->user->getId();
        parent::update($data, $condition);
    }

    function delete($condition)
    {
        $condition['intranet_id'] = $this->user->getActiveIntranetId();
        $condition['user_id'] = $this->user->getId();
        parent::delete($condition);
    }
}

class User {
    function getActiveIntranetId()
    {
        return 1;
    }

    function getId()
    {
        return 1;
    }
}

$conn = new pdoext_Connection('mysql:dbname=pear;host=localhost', 'root', '');

$gateway = new Intraface_TableGateway('debtor', $conn, new User);
$gateway->insert(array('this_date' => date('Y-d-d'), 'description' => 'testing'));

// find out how to save() and fetch() without having to do it everywhere
class Intraface_Module_Invoice_TableGateway extends Intraface_TableGateway
{
    function __construct()
    {
    }

    function save($data)
    {
        if ($this->id > 0) {
            $condition['id'] = $this->id;
            $this->update($data, $condition);
        } else {
            $this->insert($data);
        }
    }

    function getFromId($id)
    {
        // denne skal soerge for at lave en invoice gennem invoice konstruktoren
    }
}

class Intraface_Module_Invoice
{
    // her skal ikke vaere nogen som helst dataaccess i
    // men hvordan skal vi saa gemme - det skal ske gennem table gatewayen, men hvordan skal den vide, hvad der skal gemmes?
    function __construct()
    {

    }
}
?>