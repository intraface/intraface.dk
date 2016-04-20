<?php
/**
 * @package Intraface
 */
class Intraface_Webshop_FeaturedProducts
{
    /**
     * @var object
     */
    private $db;

    /**
     * @var object
     */
    private $intranet;

    /**
     * @param object $intranet Headline
     * @param object $db       Databaseobject
     *
     * @return integer
     */
    function __construct($intranet, $db)
    {
        $this->intranet = $intranet;
        $this->db       = $db;
    }

    /**
     * @param string $headline Headline
     * @param object $keyword  Keyword object
     *
     * @return integer
     */
    function add($description, $keyword)
    {
        $result = $this->db->query('SELECT id FROM shop_featuredproducts WHERE intranet_id = ' . $this->db->quote($this->intranet->getId(), 'integer') . ' AND keyword_id  = ' . $this->db->quote($keyword->getId(), 'integer'));
        if (PEAR::isError($result)) {
            throw new Exception($result->getUserInfo());
        }

        if ($result->numRows() == 0) {
            $sth = $this->db->prepare('INSERT INTO shop_featuredproducts (intranet_id, headline, keyword_id) VALUES (?, ?, ?)', array('integer', 'text', 'integer'), MDB2_PREPARE_MANIP);
        } else {
            $sth = $this->db->prepare('UPDATE shop_featuredproducts SET intranet_id = ?, headline = ?, keyword_id = ?', array('integer', 'text', 'integer'), MDB2_PREPARE_MANIP);
        }

        if (PEAR::isError($sth)) {
            throw new Exception($sth->getUserInfo());
        }

        $result = $sth->execute(array($this->intranet->getId(), $description, $keyword->getId()));
        if (PEAR::isError($result)) {
            throw new Exception($result->getUserInfo());
        }
        return true;
    }

    /**
     * @param integer $id Id for the featured product to delete
     */
    function delete($id)
    {
        $result = $this->db->query('DELETE FROM shop_featuredproducts WHERE intranet_id = ' . $this->db->quote($this->intranet->getId(), 'integer') . ' AND id  = ' . $this->db->quote($id, 'integer'));
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
