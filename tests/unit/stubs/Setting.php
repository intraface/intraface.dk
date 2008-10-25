<?php
class FakeSetting
{
    public $setting;

    function get($type, $setting)
    {
        if (!isset($this->setting[$type][$setting])) {
            trigger_error('You need to create the setting '.$type.':'.$setting.' in with set(type, key, value) before use', E_USER_ERROR);
            exit;
        }

        return $this->setting[$type][$setting];
    }

    function set($type, $key, $value)
    {
        $this->setting[$type][$key] = $value;
    }
}
