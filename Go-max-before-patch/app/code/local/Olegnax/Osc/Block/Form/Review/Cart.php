<?php 
class Olegnax_Osc_Block_Form_Review_Cart extends Mage_Checkout_Block_Onepage_Review_Info
{    
    public function updateCartURL()
    {
        return Mage::getUrl('onestepcheckout/ajax/cartUpdate', array('_secure'=>true));
    }        
    
    public function isCartEditable()
    {
		/* Upgrade to Premium */
    }    
}
?>