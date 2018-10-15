<?php
class Olegnax_Osc_Block_Form_Shippingmethod extends Mage_Checkout_Block_Onepage_Abstract
{
    protected $_address;
    protected $_rates;    
    
    public function getAddress()
    {
        if (empty($this->_address)) { $this->_address = $this->getQuote()->getShippingAddress(); }
        return $this->_address;
    }    
    
    public function getShippingRates()
    {
        if (empty($this->_rates)) 
        {
            $this->getAddress()->collectShippingRates()->save();
            return $this->_rates = $this->getAddress()->getGroupedAllShippingRates();
        }
        return $this->_rates;
    }    
    
    public function getCarrierName($carrierCode)
    {
        if ($carrierName = Mage::getStoreConfig('carriers/'.$carrierCode.'/title')) { return $carrierName; }
        return $carrierCode;
    }
    
    //DONE+
    public function canShip()
    {
        return !$this->getQuote()->isVirtual();
    }
    
    //DONE+
    public function getAddressShippingMethod()
    {
        return $this->getAddress()->getShippingMethod();
    }
    
    //DONE+
    public function getShippingPrice($price, $flag)
    {
        return $this->getQuote()->getStore()->convertPrice(Mage::helper('tax')->getShippingPrice($price, $flag, $this->getAddress()), true);
    }
    
    //DONE+
    public function getSaveShipmentUrl()
    {
        return Mage::getUrl('olegnax_osc/ajax/shippingSave');
    }
    
    //DONE+
    public function getBlockNumber($isIncrementNeeded = true)
    {
        return Mage::helper('olegnax_osc')->numberOfBlocks($isIncrementNeeded);
    }
    
    //DONE+
    public function getEnterpriseGiftWrappingHtml()
    {
        if (Mage::helper('core')->isModuleEnabled('Enterprise_GiftWrapping')) 
        {                
            return Mage::app()->getLayout()->createBlock('enterprise_giftwrapping/checkout_options')->setTemplate('giftwrapping/checkout/options.phtml')->toHtml() . Mage::app()->getLayout()->createBlock('olegnax_osc/form_shipping_enterprise_giftwrap')->setTemplate('olegnax_osc/form/shipping/enterprise/giftwrap.phtml')->toHtml();
        }
        return '';
    }
}