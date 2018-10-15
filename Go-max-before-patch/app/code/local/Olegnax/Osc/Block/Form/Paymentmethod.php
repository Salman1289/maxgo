<?php
class Olegnax_Osc_Block_Form_Paymentmethod extends Mage_Checkout_Block_Onepage_Payment_Methods
{
    public function getMethods()
    {
        $currentMethods = $this->getData('methods');
        if (is_null($currentMethods))
        {
            $quote = $this->getQuote();
            $currentStore = null;
            if($quote)
            {
                $currentStore = $quote->getStoreId();
            }            
            $currentMethods = $this->helper('payment')->getStoreMethods($currentStore, $quote);
            $uberTotal = $quote->getBaseGrandTotal();
            foreach ($currentMethods as $key => $method) 
            {
                if ($this->_canUseMethod($method) && ($uberTotal != 0 || $method->getCode() == 'free' || ($quote->hasRecurringItems() && $method->canManageRecurringProfiles())))
                {
                    $this->_assignMethod($method);
                } 
                else
                {
                    unset($currentMethods[$key]);
                }
            }
            $this->setData('methods', $currentMethods);
        }
        return $currentMethods;
    }
    
    public function savePaymentUrl()
    {
        return Mage::getUrl('olegnax_osc/ajax/paymentSave');
    } 
    
    public function numberOfBlock($isIncrementNeeded = true)
    {
        return Mage::helper('olegnax_osc')->numberOfBlocks($isIncrementNeeded);
    }
}
?>