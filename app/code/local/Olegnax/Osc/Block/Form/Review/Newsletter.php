<?php 
class Olegnax_Osc_Block_Form_Review_Newsletter extends Mage_Checkout_Block_Onepage_Abstract
{
    protected $subscription = null;
    protected $customer = null;
    
    public function getIsSubscribed()
    {
        $sessionData = Mage::getSingleton('checkout/session')->getData('olegnax_osc_form_values');
        if (isset($sessionData['is_subscribed'])) { return $sessionData['is_subscribed']; }
        return false;
    }
    
    public function subscriptionObject()
    {
        if (is_null($this->subscription)) 
        {
            $this->subscription = Mage::getModel('newsletter/subscriber');
            if ($this->getCurrentCustomer()->getId()) { $this->subscription->loadByCustomer($this->getCurrentCustomer()); }
        }
        return $this->subscription;
    }    
    
    public function canShowSimpleNewsletter()
    {
        if (!Mage::helper('olegnax_osc')->isMageNewsletterEnabled()) { return false; }
        if ($this->isSubscribed()) { return false; }
        return true;
    } 
    
    public function isSubscribed()
    {
        if (!is_null($this->subscriptionObject()))
        {
            return $this->subscriptionObject()->isSubscribed();
        }
        return false;
    }    
    
    public function getCurrentCustomer()
    {
        if (is_null($this->customer))
        {
            $this->customer = Mage::getSingleton('customer/session')->getCustomer();
        }
        return $this->customer;
    }    
    
    public function saveValuesUrl()
    {
        return Mage::getUrl('olegnax_osc/ajax/saveFormValues');
    }
    
    public function showNewsletter()
    {
        if (!Mage::helper('olegnax_osc/config')->isNewsletter()) {return false;}
        return true;
    }
}
?>