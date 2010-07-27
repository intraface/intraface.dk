<?php
/**
 * @package Intraface_Procurement
 */

$procurement_module = $kernel->useModule('procurement');

$procurement = new Intraface_modules_procurement_ProcurementGateway($kernel);

if (!$procurement->any()):
    $_advice[] = array(
        'msg' => 'you can create new procurements',
        'link' => $procurement_module->getPath(),
        'module' => $procurement_module->getName()
    );
endif;