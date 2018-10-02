<?php 
class Olegnax_Osc_Block_Premium extends Mage_Adminhtml_Block_System_Config_Form_Field{
	public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '<p style="text-align:center;"><a href="http://http://olegnax.com/product/one-step-checkout-magento-extension/" target="_blank"><img src="'.Mage::getBaseUrl('skin') . '/frontend/base/default/olegnax_osc/images/premium_one_step_checkout.jpg" alt=""/></a></p>';
        return $html;
    }
}
