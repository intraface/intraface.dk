<?php
function fht_deltree($f)
{

    if (is_dir($f)) {
        foreach (scandir($f) as $item) {
            if (!strcmp($item, '.') || !strcmp($item, '..')) {
                continue;
            }
            fht_deltree($f . "/" . $item);
        }
        rmdir($f);
    } else{
        @unlink($f);
    }
}


function iht_deltree($f)
{

    if (is_dir($f)) {
        foreach (scandir($f) as $item) {
            if (!strcmp($item, '.') || !strcmp($item, '..')) {
                continue;
            }
            iht_deltree($f . "/" . $item);
        }
        rmdir($f);
    } else{
        @unlink($f);
    }
}
