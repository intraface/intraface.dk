<?php
/**
 * @package Intraface_Administration
 */

$administration_module = $kernel->useModule('administration');

$intranet = new IntranetAdministration($kernel);

if (!$intranet->isFilledIn()) :
    $_advice[] = array(
        'msg' => 'all information about the intranet has not been filled in',
        'link' => $administration_module->getPath() . 'intranet?edit',
        'module' => $administration_module->getName()
    );
endif;

if (!$intranet->get('identifier')) :
    $_attention_needed[] = array(
        'msg' => 'identifier for the intranet is missing',
        'link' => $administration_module->getPath() . 'intranet?edit',
        'module' => $administration_module->getName()
    );
endif;
