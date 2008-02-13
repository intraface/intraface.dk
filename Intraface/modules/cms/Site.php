<?php
/**
 * Spørgsmålet er hvornår stylesheet og sitemap skal loades
 * - Vi kan loade dem pï¿½ request i load()?
 * Navigation skal vel fï¿½lge den enkelte side?
 *
 * Vi skal have sat encoding-muligheder (kun iso i ï¿½jeblikket) og language her.
 *
 * @package Intraface_CMS
 * @author Lars Olesen <lars@legestue.net>
 * @since	1.0
 * @version	1.0
 */

require_once 'Intraface/Standard.php';
require_once 'Stylesheet.php';

class CMS_Site extends Standard
{
    public $id;
    public $kernel;
    public $error;
    public $stylesheet;
    public $value;

    function __construct($kernel, $id = 0)
    {
        if (!is_object($kernel) OR strtolower(get_class($kernel)) != 'kernel') {
            trigger_error('CMS_Site::__construct needs kernel', E_USER_ERROR);
        }

        $this->kernel = $kernel;
        $this->id = (int)$id;
        $this->error = new Error;

        if ($this->id > 0) {
            $this->load();
        }

        $this->stylesheet = new CMS_Stylesheet($this);
    }

    function validate($var)
    {
        $validator = new Validator($this->error);
        $validator->isString($var['name'], 'error in name', '');
        $validator->isUrl($var['url'], 'error in url', 'allow_empty');
        if (substr($var['url'], -1) != '/') {
            $this->error->set('error in url - has to end with a slash');
        }
        $validator->isUrl($var['url'], 'error in url', 'allow_empty');
        if (substr($var['url'], 0, 7) != 'http://' AND substr($var['url'], 0, 8) != 'https://') {
            $this->error->set('error in url - start with http');
        }
        $validator->isNumeric($var['cc_license'], 'error in cc license');


        if ($this->error->isError()) {
            return 0;
        }
        return 1;
    }

    function save($var)
    {
        $var = safeToDb($var);

        settype($var['cc_license'], 'integer');
        if (!$this->validate($var)) {
            return 0;
        }

        if ($this->id > 0) {
            $sql_type = "UPDATE ";
            $sql_end = " WHERE id = " . $this->id;
        } else {
            $sql_type = "INSERT INTO ";
            $sql_end = " , date_created = NOW()";
        }

        $db = new DB_Sql;
        $db->query($sql_type . " cms_site
            SET intranet_id = ".$this->kernel->intranet->get('id').",
            name = '".$var['name']."',
            url = '".$var['url']."',
            date_updated = NOW()" . $sql_end);

        if ($this->id == 0) {
            $this->id = $db->insertedId();
        }

        $this->kernel->setting->set('intranet', 'cc_license', intval($var['cc_license']), 'site_id_' . $this->id);

        $this->load();

        return $this->id;
    }

    function load()
    {
        $db = new DB_Sql;
        $db->query("SELECT id, name, url FROM cms_site WHERE id = " . $this->id . " AND intranet_id = ".$this->kernel->intranet->get('id')." LIMIT 1");
        if (!$db->nextRecord()) {
            return 0;
        }
        $this->value['id'] = $db->f('id');
        $this->value['name'] = $db->f('name');
        $this->value['url'] = $db->f('url');
        $this->value['cc_license'] = $this->kernel->setting->get('intranet', 'cc_license', 'site_id_' . $this->get('id'));

    }

    function delete()
    {
        if ($this->id == 0) {
            $this->error->set('Kunne ikke slette');
            return 0;
        }
        $db = new Db_Sql;
        $db->query("UPDATE cms_site SET active = 0 WHERE intranet_id=".$this->kernel->intranet->get('id')." AND id = " . $this->id);
        return 1;
    }

    function getList()
    {
        $db = new DB_Sql;
        $db->query("SELECT id, name FROM cms_site WHERE intranet_id = " . $this->kernel->intranet->get('id'). " AND active = 1");
        $i = 0;
        $sites = array();
        while ($db->nextRecord()) {
            $sites[$i]['id'] = $db->f('id');
            $sites[$i]['name'] = $db->f('name');
            $i++;
        }
        return $sites;
    }
}