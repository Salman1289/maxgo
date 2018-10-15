<?php
class Olegnax_Osc_Block_Form_Review_Enterprise_Giftcard extends Mage_Checkout_Block_Onepage_Abstract
{    
    public function canShowGiftCard()
    {
        if (Mage::helper('olegnax_osc')->giftcardEnabled()) { return true; }
        return false;
    }    
    
    public function applyEnterpriseAjaxUrl()
    {
        return Mage::getUrl('onestepcheckout/ajax/confirmGiftcardForEnterprise', array('_secure' => true));
    }    
    
    public function dellEnterpriseAjaxUrl()
    {
        return Mage::getUrl('onestepcheckout/ajax/dellGiftcardForEnterprise', array('_secure' => true));
    }
}