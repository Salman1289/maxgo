<?php
class Olegnax_Osc_Block_Form_Address extends Mage_Checkout_Block_Onepage_Abstract
{ 
    public function changedUrl()
    {
        return Mage::getUrl('onestepcheckout/ajax/addressSave');
    }    
    
    public function getSaveFormValuesUrl()
    {
        return Mage::getUrl('olegnax_osc/ajax/saveFormValues');
    }
   
    public function canShip()
    {
        return !$this->getQuote()->isVirtual();
    }
    
    public function useBillingForShipping()
    {
        return $this->getConfiguration()->billingForShipping();
    }
    
    public function getConfiguration()
    {
        return Mage::helper('olegnax_osc/config');
    }    
    
    public function getBlockNumber($isIncrementNeeded = true)
    {
        return Mage::helper('olegnax_osc')->numberOfBlocks($isIncrementNeeded);
    } 
}
?>