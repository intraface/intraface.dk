<?php
class Intraface_Date
{
    private $date;

    function __construct($date)
    {
        $this->date = $date;
    }

    function convert2db($default_year = "")
    {

        /**
         * HUSK AT RETTE I BÅDE VALIDATOR OG DATE
         */

        $d = "([0-3]?[0-9])";
        $m = "([0-1]?[0-9])";
        $y = "([0-9][0-9][0-9][0-9]|[0-9]?[0-9])";
        $s = "(-|\.|/| )";

        if ($default_year == "") {
            $default_year = date("Y");
        }

        if (ereg("^".$d.$s.$m.$s.$y."$", $this->date, $parts)) {
            // true
        } elseif (ereg("^".$d.$s.$m."$", $this->date, $parts)) {
            $parts[5] = $default_year;
            // true
        } else {
            return false;
        }

        $this->date = $parts[5]."-".$parts[3]."-".$parts[1];
        return true;
    }

    function get()
    {
        return $this->date;
    }
}