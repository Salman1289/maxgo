<?php
class Olegnax_Osc_Block_Form_Review_Enterprise_Storecredit extends Mage_Checkout_Block_Onepage_Abstract
{
    public function creditAvailable()
    {
        return Mage::helper('olegnax_osc')->creditAvailable();
    }    
    
    public function getBalance()
    {
        return Mage::helper('olegnax_osc')->getBalance();
    }
    
    public function priceFormat($value)
    {
        return Mage::getSingleton('adminhtml/session_quote')->getStore()->formatPrice($value);
    }    
    
    public function canShowCredit()
    {
        if (Mage::helper('olegnax_osc')->creditEnabled()) { return true; }
        else { return false; }
    }
   
    public function balanceUsed()
    {
        return Mage::helper('olegnax_osc')->balanceUsed();
    }
    
    public function applyAjaxUrl()
    {
        return Mage::getUrl('onestepcheckout/ajax/confirmStorecreditForEnterprise', array('_secure' => true));
    }
}
?>