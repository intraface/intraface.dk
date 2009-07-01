<?php
class Intraface_modules_currency_Currency_Type
{
    public function __construct()
    {

    }

    public function getAll()
    {
        if (!($handle = opendir(dirname(__FILE__).DIRECTORY_SEPARATOR.'Type'))) {
            throw new Exception('Unable to read currencies dir');
        }

        $currencies = array();

        while (false !== ($file = readdir($handle))) {
            if (strlen($file) == '7' && substr($file, 3) == '.php') {
                $class_name = 'Intraface_modules_currency_Currency_Type_'.substr($file, 0, 3);
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

        if (!file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.'Type'.DIRECTORY_SEPARATOR.$iso_code.'.php')) {
            throw new Exception('The currecy '.$iso_code.' does not exist');
        }

        $class_name = 'Intraface_modules_currency_Currency_Type_'.$iso_code;
        return new $class_name;
    }

    public function getByKey($key)
    {
        $types = $this->getAll();
        foreach ($types AS $type) {
            if ($type->getKey() == $key) {
                return $type;
            }
        }

        throw new Exception('Invalid key '.$key);
    }
}
