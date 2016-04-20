<?php
class Intraface_Doctrine_IntranetGateway
{
    function findByIntranetId($id)
    {
        return Intraface_Doctrine_Intranet::singleton($id);
    }
}
