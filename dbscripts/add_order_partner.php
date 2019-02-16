<?php


require_once('../app/Mage.php');
Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
$installer = new Mage_Sales_Model_Mysql4_Setup;
$attribute  = array(
        'type'          => 'text',
        'backend_type'  => 'text',
        'frontend_input' => 'text',
        'is_user_defined' => true,
        'label'         => 'Partner',
        'visible'       => true,
        'required'      => false,
        'user_defined'  => false,   
        'searchable'    => true,
        'filterable'    => true,
        'comparable'    => true,
);
if($installer->addAttribute('order', 'partner', $attribute)){
        echo "string";
}