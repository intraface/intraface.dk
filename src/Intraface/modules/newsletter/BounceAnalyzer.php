<?php
class Intraface_modules_newsletter_BounceAnalyzer
{
    function __construct()
    {

    }

    function isSoftBounce($body)
    {
        if (stripos($body, 'mailbox full') !== false) {
            return true;
        }
        if (stripos($body, 'quota exeeded') !== false) {
            return true;
        }
    }

    function isHardBounce($body)
    {
        if (stripos($body, 'unknown user') !== false) {
            return true;
        }

    }
}
