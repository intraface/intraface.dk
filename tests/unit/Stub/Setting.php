<?php
class Stub_Setting
{
    public $setting;

    function get($type, $setting)
    {
        if (!isset($this->setting[$type][$setting])) {
            throw new Exception('You need to create the setting '.$type.':'.$setting.' in with set(type, key, value) before use');
        }

        return $this->setting[$type][$setting];
    }

    function set($type, $key, $value)
    {
        $this->setting[$type][$key] = $value;
    }
}
