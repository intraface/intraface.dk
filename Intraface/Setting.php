<?php
/**
 * Håndterer Settings i systemet
 *
 * Tabelfelter: id, intranet_id, user_id, setting, value, sub_id
 * Settingniveauer: System, Intranet, User
 *
 * @author Sune Jensen <sj@sunet.dk>
 */
require_once 'Intraface/functions.php';

class Intraface_Setting
{
    /**
     * @var object
     */
    private $db;

    /**
     * @var array
     */
    private $system;

    /**
     * @var integer
     */
    private $user_id;

    /**
     * @var integer
     */
    private $intranet_id;

    /**
     * @var array
     */
    protected $settings = array();

    /**
     * @var boolean
     */

    protected $is_loaded = false;

    /**
     * Checks whether setting is in system
     *
     * @param integer $intranet_id
     * @param integer $user_id
     *
     * @return void
     */
    function __construct($intranet_id, $user_id = 0)
    {
        global $_setting;

        require_once 'Intraface/config/setting_kernel.php';

        // Init
        $this->db = new DB_Sql;
        $this->system = &$_setting; // don't remove the & - otherwise it will not work

        $this->user_id = (int)$user_id;
        $this->intranet_id = (int)$intranet_id;
    }

    /**
     * Checks whether setting is in system
     *
     * @param string $setting to test
     *
     * @return boolean or throws exception
     */
    private function checkSystem($setting)
    {
        if (!empty($setting) && is_array($this->system) && isset($this->system[$setting])) {
            return true;
        } else {
            throw new Exception('Setting "'.$setting.'" is not defined');
        }
    }

    /**
     * Checks whether type is a valid type
     *
     * @param string $type to test
     *
     * @return boolean or throws exception
     */
    private function checkType($type)
    {
        if ($type == 'system' || $type == 'intranet' || $type == 'user') {
            return true;
        } else {
            trigger_error('Ugyldig type setting "'.$type.'"', E_USER_ERROR);
        }
    }

    /**
     * Checks whether the user is logged in
     *
     * @return boolean
     */
    private function checkLogin()
    {
        if ($this->user_id != 0) {
            return true;
        } else {
            trigger_error('Du kan ikke udføre denne handling fra et weblogin', E_USER_ERROR);
        }
    }

    /**
     * Sets a certain setting
     *
     * @access protected, however to be tested we kept it public
     *
     * @param string  $type    Can be either system, intranet, user
     * @param string  $setting The actual setting
     * @param integer $sub_id  @todo What is this exactly
     *
     * @return boolean
     */
    function set($type, $setting, $value, $sub_id = 0) {

        if ($this->checkSystem($setting) && $this->checkType($type) && $this->checkLogin()) {

            switch ($type) {
                case 'system':
                    throw new Exception('Du kan ikke ændre på systemsetting');
                    break;
                case 'intranet':
                    $this->db->query("SELECT id FROM setting WHERE setting = ".$this->db->quote($setting, 'text')." AND intranet_id = ".$this->intranet_id." AND user_id = 0 AND sub_id = ".intval($sub_id));
                    if ($this->db->nextRecord()) {
                        $this->db->query("UPDATE setting SET value = ".$this->db->quote($value, 'text')." WHERE id = ".$this->db->quote($this->db->f("id"), 'integer'));
                    } else {
                        $this->db->query("INSERT INTO setting SET value = ".$this->db->quote($value, 'text').", setting = ".$this->db->quote($setting, 'text').", intranet_id = ".$this->db->quote($this->intranet_id, 'integer').", user_id = 0, sub_id = ".intval($sub_id));
                    }
                    $this->settings[$this->intranet_id][0][$setting][$sub_id] = $value;
                break;
                case 'user':
                    if ($this->checkSystem($setting)) {
                        $this->db->query("SELECT id FROM setting WHERE setting = ".$this->db->quote($setting, 'text')." AND intranet_id = ".$this->db->quote($this->intranet_id, 'integer')." AND user_id = ".$this->db->quote($this->user_id, 'integer')." AND sub_id = ".intval($sub_id));
                        if ($this->db->nextRecord()) {
                            $this->db->query("UPDATE setting SET value = ".$this->db->quote($value, 'text')." WHERE id = ".$this->db->quote($this->db->f("id"), 'integer'));
                        } else {
                            $this->db->query("INSERT INTO setting SET value = ".$this->db->quote($value, 'text').", setting = ".$this->db->quote($setting, 'text').", intranet_id = ".$this->intranet_id.", user_id = ".$this->db->quote($this->user_id, 'integer').", sub_id = ".intval($sub_id));
                        }
                    }
                    $this->settings[$this->intranet_id][$this->user_id][$setting][$sub_id] = $value;
                    break;
            }
            return true;
        }
        return false;
    }

    /**
     * Checks whether a setting is set
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Loads settings
     *
     * @return void
     */
    private function loadSettings()
    {
        $this->settings = array();
        $this->db->query("SELECT setting, value, sub_id, user_id FROM setting WHERE intranet_id = " . $this->db->quote($this->intranet_id, 'integer')." AND (user_id = ".$this->db->quote($this->user_id, 'integer')." OR user_id = 0)");
        while($this->db->nextRecord()) {
            $this->settings[$this->intranet_id][$this->db->f('user_id')][$this->db->f('setting')][$this->db->f('sub_id')] = $this->db->f('value');
        }
        $this->is_loaded = true;
    }

    /**
     * Returns whether the settings has already been loaded
     *
     * @return boolean
     */
    private function isLoaded()
    {
        return $this->is_loaded;
    }

    /**
     * Gets a certain setting
     *
     * @access protected, however to be tested we kept it public
     *
     * @param string  $type    Can be either system, intranet, user
     * @param string  $setting The actual setting
     * @param integer $sub_id  @todo What is this exactly
     *
     * @return boolean
     */
    public function get($type, $setting, $sub_id = 0)
    {
        if (!$this->isLoaded()) {
            $this->loadSettings();
        }

        if ($this->checkSystem($setting) && $this->checkType($type)) {
            switch($type) {
                case 'user':
                    if ($this->checkLogin()) {
                        // hvis der ikke er nogen intranet-indstillinger på posten vil den stadig
                        // blive ved med at lave opslaget. Hvordan undgår vi lige det på en god og sikker måde?
                        /*
                        if (!isset($this->settings['user'])) {
                            $this->settings['user'] = array();
                            $this->db->query("SELECT setting, value, sub_id FROM setting WHERE intranet_id = ".$this->intranet_id." AND user_id = ".$this->user_id);
                            while($this->db->nextRecord()) {
                                $this->settings['user'][$this->db->f('setting')][$this->db->f('sub_id')] = $this->db->f('value');
                            }

                        }
                        */
                        if (!empty($this->settings[$this->intranet_id][$this->user_id][$setting][intval($sub_id)])) {
                            return $this->settings[$this->intranet_id][$this->user_id][$setting][intval($sub_id)];
                        }


                    }
                    // no break because it has to fall through if user is not set
                case 'intranet':
                    // hvis der ikke er nogen intranet-indstillinger på posten vil den stadig
                    // blive ved med at lave opslaget. Hvordan undgår vi lige det.
                    /*
                    if (!isset($this->settings['intranet'])) {
                        $this->settings['intranet'] = array();
                        $this->db->query("SELECT setting, value, sub_id FROM setting WHERE intranet_id = ".$this->intranet_id." AND user_id = 0");

                        while($this->db->nextRecord()) {
                            $this->settings['intranet'][$this->db->f('setting')][intval($this->db->f('sub_id'))] = $this->db->f('value');
                        }

                    }
                    */
                    if (!empty($this->settings[$this->intranet_id][0][$setting][intval($sub_id)])) {
                        return $this->settings[$this->intranet_id][0][$setting][intval($sub_id)];
                    }
                    // no break because it has to fall through if intranet is not set
                default:
                    return $this->system[$setting];
                    break;
            }
        }
    }

    /**
     * Checks whether a setting is set
     *
     * @access protected, however to be tested we kept it public
     *
     * @param string  $type    Can be either system, intranet, user
     * @param string  $setting The actual setting
     * @param integer $sub_id  @todo What is this exactly
     *
     * @return boolean
     */
    function isSettingSet($type, $setting, $sub_id = 0)
    {
        if ($this->checkSystem($setting) && $this->checkType($type)) {
            switch($type) {
                case 'user':
                    if ($this->checkLogin()) {
                        $this->db->query("SELECT value FROM setting WHERE setting = \"".$setting."\" AND intranet_id = ".$this->intranet_id." AND user_id = ".$this->user_id." AND sub_id = ".intval($sub_id));
                        if ($this->db->nextRecord()) {
                            return true;
                        }
                    }
                    break;

                case 'intranet':
                    $this->db->query("SELECT value FROM setting WHERE setting = \"".$setting."\" AND intranet_id = ".$this->intranet_id." AND user_id = 0 AND sub_id = ".intval($sub_id));
                    if ($this->db->nextRecord()) {
                        return true;
                    }
                    break;

                default:
                    return true;
                    break;
            }
        }
        return false;
    }

    /**
     * Deletes a setting
     *
     * @access protected, however to be tested we kept it public
     *
     * @param string  $type    Can be either system, intranet, user
     * @param string  $setting The actual setting
     * @param integer $sub_id  @todo What is this exactly
     *
     * @return boolean
     */
    function delete($type, $setting, $sub_id = 0) {

        if ($this->checkSystem($setting) && $this->checkType($type) && $this->checkLogin()) {

            if ($sub_id == 'ALL') {
                $sql_sub = '';
            } else {
                $sql_sub = "AND sub_id = ".intval($sub_id);
            }

            switch ($type) {
                case 'user':
                    $this->db->query("DELETE FROM setting WHERE setting = \"".$setting."\" AND intranet_id = ".$this->intranet_id." AND user_id = ".$this->user_id." ".$sql_sub);
                    $return = true;
                    break;

                case 'intranet':
                    $this->db->query("DELETE FROM setting WHERE setting = \"".$setting."\" AND intranet_id = ".$this->intranet_id." ".$sql_sub);
                    $return = true;
                    break;

                default:
                    trigger_error('Du kan ikke slette en system setting', E_USER_ERROR);
                    return false;
            }
        }

        $this->loadSettings();

        return $return;
    }
}