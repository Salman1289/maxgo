<?php 
class Olegnax_Osc_Block_Form_Shipping_Enterprise_Giftwrap extends Mage_Checkout_Block_Onepage_Abstract
{    
    public function printedCardUrl()
    {
        return Mage::getUrl('onestepcheckout/ajax/setPrintedCardForEnterprise', array('_secure' => true));
    }    
    
    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }    
}
?>