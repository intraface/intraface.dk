<?php
/**
 * @package Intraface_Invoice
 */
if($kernel->user->hasModuleAccess('modulepackage')) {

    $modulepackage_module = $kernel->useModule('modulepackage');
    require_once 'Intraface/modules/modulepackage/Manager.php';
    $manager = new Intraface_ModulePackage_Manager($kernel->intranet);
    
    $manager->createDBQuery($kernel);
    $manager->dbquery->setFilter('status', 'created_and_active');
    $list = $manager->getList();
   

    if (count($list) == 0) {
        
        $_attention_needed[] = array(
            'module' => $modulepackage_module->getName(),
            'link' => $modulepackage_module->getPath(),
            'msg' => 'you have no modules in your intranet! click here to add your desired modules',
            'no_translation' => true
        );
    }
    else {
        
        foreach($list AS $key => $module) {
            // check whether there is any expering within a month and if there is no other packages comming up in the same group.
            if(strtotime($module['end_date']) < strtotime('+1 month') && (!isset($list[$key+1]) || $module['group'] != $list[$key+1]['group'])) {
                $_advice[] = array(
                    'msg' => 'you have modules that expire within a month! click here to extend the modules now to keep the functionality of your intranet',
                    'link' => $modulepackage_module->getPath(),
                    'module' => 'modulepackage'
                );
                break;
            }
        }      
    }

}
?>
