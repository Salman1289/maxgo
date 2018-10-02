<?php 
class Olegnax_Osc_Block_Field_Disabled extends Mage_Adminhtml_Block_System_Config_Form_Field{
    protected function _getElementHtml($element) {       
        $element->setDisabled('disabled');
        return parent::_getElementHtml($element);
	}
}
?>