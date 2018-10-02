<?php
class Olegnax_Osc_Block_Onestepcheckout extends Mage_Checkout_Block_Onepage_Abstract
{
    public function placeOrderUrl()
    {
        return Mage::getUrl('onestepcheckout/ajax/confirmOrder', array('_secure'=>true));
    }	
    
    public function getMap()
    {
		$result = array();
        $updaterModel = Mage::getModel('olegnax_osc/updater');        
        foreach($updaterModel->getMap() as $actionKey => $blocks) $result[$actionKey] = array_keys($blocks);
        return $result;
    }
	
	public function blockNumber($incrementNeeded = true)
    {
        return Mage::helper('olegnax_osc')->numberOfBlocks($incrementNeeded);
    }
	
	public function getUberTotal()
    {
        return Mage::helper('olegnax_osc')->getUberTotal($this->getQuote());
    }
    
    public function getDescription()
    {
        return Mage::helper('olegnax_osc/config')->descriptionCheckout();
    }
    
    public function getTitle()
    {
        return $this->escapeHtml(Mage::helper('olegnax_osc/config')->titleCheckout());
    }
    
    public function canShowAuth()
    {
        if (!(Mage::getSingleton('customer/session')->isLoggedIn())) { return true; }
        else { return false; }
    }
    
    public function forgotPasswordAjaxURL()
    {
        return Mage::getUrl('onestepcheckout/ajax/forgotPass', array('_secure'=>true));
    }
    
    public function getUsername()
    {        
        return $this->escapeHtml(Mage::getSingleton('customer/session')->getUsername(true));
    }
    
    public function loginAjaxURL()
    {
        return Mage::getUrl('onestepcheckout/ajax/logIn', array('_secure'=>true));
    }
    
    public function changedUrl()
    {
        return Mage::getUrl('onestepcheckout/ajax/addressSave');
    }
    
    public function getSaveFormValuesUrl()
    {
        return Mage::getUrl('olegnax_osc/ajax/saveFormValues');
    }
}