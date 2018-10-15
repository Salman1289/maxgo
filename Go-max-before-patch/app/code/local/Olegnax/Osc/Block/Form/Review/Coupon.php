<?php 
class Olegnax_Osc_Block_Form_Review_Coupon extends Mage_Checkout_Block_Onepage_Abstract
{    
    public function getCouponCode()
    {
        return $this->getQuote()->getCouponCode();
    }    
     
    public function cancelCouponUrl()
    {
        return Mage::getUrl('onestepcheckout/ajax/cancelCoupon', array('_secure'=>true));
    }    
   
    public function confirmCouponUrl()
    {
        return Mage::getUrl('onestepcheckout/ajax/confirmCoupon', array('_secure'=>true));
    }    
    
    public function getConfig()
    {
        return Mage::helper('olegnax_osc/config');
    }    
    
    public function getAppliedPoints()
    {
        return Mage::helper('olegnax_osc')->appliedPoints();
    }   

    public function canShowCoupon()
    {             
        return Mage::helper('olegnax_osc/config')->isCoupon();
    }	
}
?>