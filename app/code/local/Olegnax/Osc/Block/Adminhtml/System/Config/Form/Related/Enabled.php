<?php

class Olegnax_Osc_Block_Adminhtml_System_Config_Form_Related_Enabled extends Mage_Adminhtml_Block_System_Config_Form_Field
{    
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $currentElement)
    {        
        $currentElement->setScope(null);
        return parent::_getElementHtml($currentElement);
    }
}
?>