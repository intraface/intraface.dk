<?php
class Intraface_modules_language_Gateway
{
    public function getAll()
    {
        if (!($handle = opendir(dirname(__FILE__).DIRECTORY_SEPARATOR.'Language'))) {
            throw new Exception('Unable to read currencies dir');
        }

        $currencies = array();

        while (false !== ($file = readdir($handle))) {
            if (substr($file, -4) == '.php') {
                $class_name = 'Intraface_modules_language_Language_'.substr($file, 0, -4);
                $object = new $class_name;
                $currencies[$object->getIsoCode()] = $object;
            }
        }

        ksort($currencies);

        return $currencies;
    }

    public function getByIsoCode($iso_code)
    {
        $iso_code = ucfirst(strtolower($iso_code));
        if (!ereg("^[A-Z][a-z]{2}$", $iso_code)) {
            throw new Exception('Invalid iso code '.$iso_code);
        }

        if (!file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.'Language'.DIRECTORY_SEPARATOR.$iso_code.'.php')) {
            throw new Exception('The currecy '.$iso_code.' does not exist');
        }

        $class_name = 'Intraface_modules_language_Language_'.$iso_code;
        return new $class_name;
    }

    public function getByKey($key)
    {
        $types = $this->getAll();
        foreach ($types as $type) {
            if ($type->getKey() == $key) {
                return $type;
            }
        }

        throw new Exception('Invalid key '.$key);
    }
}
