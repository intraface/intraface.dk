<?php

class Intraface_Webshop_FeaturedProducts
{
    private $db;
    private $intranet;

    function __construct($intranet, $db)
    {
        $this->intranet = $intranet;
        $this->db = $db;
    }

    function add($description, $keyword)
    {
        $sth = $this->db->prepare('INSERT INTO shop_featuredproducts (intranet_id, headline, keyword_id) VALUES (?, ?, ?)', array('integer', 'text', 'integer'), MDB2_PREPARE_MANIP);
        if (PEAR::isError($sth)) {
            throw new Exception($sth->getUserInfo());
        }

        $result = $sth->execute(array($this->intranet->getId(), $description, $keyword->getId()));
        if (PEAR::isError($result)) {
            throw new Exception($result->getUserInfo());
        }
        return true;
    }

    function getAll()
    {
        $result = $this->db->query('SELECT * FROM shop_featuredproducts WHERE intranet_id = ' . $this->db->quote($this->intranet->getId(), 'integer'));
        if (PEAR::isError($result)) {
            throw new Exception($result->getUserInfo());
        }

        return $result->fetchAll(MDB2_FETCHMODE_ASSOC);

    }
}