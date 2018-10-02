<?php
class Olegnax_Osc_Helper_Data extends Mage_Core_Helper_Data
{
    const STORAGE_KEY = 'olegnax-osc-block-number';
    protected $_weights = array();
    protected $blockCredits;
    protected $_pointsBlock;    
   
    public function loggedReq()
    {        
        if (method_exists(Mage::helper('checkout'), 'isCustomerMustBeLogged')) { return Mage::helper('checkout')->isCustomerMustBeLogged(); }
        return false;
    }    
    
    public function numberOfBlocks($incrementFlag = true)
    {        
        if (!Mage::helper('olegnax_osc/config')->numberOfBlocks()) { return null; }
        $number = Mage::registry(self::STORAGE_KEY);
        if (is_null($number)) { $number = 0; }
        $number++;
        if ($incrementFlag) 
        {            
            Mage::register(self::STORAGE_KEY, $number);
            Mage::unregister(self::STORAGE_KEY);
        }
        return $number;
    }    
    
    public function getUberTotal($currentQuote)
    {        
        return Mage::app()->getStore()->getCurrentCurrency()->format($currentQuote->getGrandTotal(), array(), false);
    }    
    
    protected function _compareBlocks($block1, $block2)
    {
        $weight1 = $this->_weights[$block1];
        $weight2 = $this->_weights[$block2];
        if ($weight1 < $weight2) { return -1; }            
        elseif ($weight1 > $weight2) { return 1; }
        else { return 0; }
    }    
    
    public function orderedBlocks($blocks)
    {
        $result = array();        
        $this->_weights = array();
        foreach ($blocks as $key => $block)
        {
            $currentColumn = Mage::helper('olegnax_osc/config')->getColumn($key);
            $this->_weights[$key] = Mage::helper('olegnax_osc/config')->orderBlocks($key);
            if ( ! isset($result[$currentColumn])) { $result[$currentColumn] = array(); }                
            $result[$currentColumn][$key] = $block;
        }
        ksort($result);
        foreach ($result as $currentColumn => &$blocks) { uksort($blocks, array($this, '_compareBlocks')); }
        unset($blocks);
        return $result;
    }
   
    public function sendPassword(Mage_Customer_Model_Customer $customer)
    {
        if (!method_exists(Mage::helper('customer'), 'generateResetPasswordLinkToken'))
        {            
            $customer->changePassword($customer->generatePassword(), false);
            $customer->sendPasswordReminderEmail();
        } 
        else 
        {            
            $customer->changeResetPasswordLinkToken(Mage::helper('customer')->generateResetPasswordLinkToken());
            $customer->sendPasswordResetConfirmationEmail();
        }
    }    
   
    protected function getLastMethod()
    {
        $currentCustomer = Mage::getSingleton('customer/session')->getCustomer();
        if (!$currentCustomer->getId()) { return false; }
        $lastOrder = Mage::getResourceModel('sales/order_collection')->addFilter('customer_id', $currentCustomer->getId())->addAttributeToSort('created_at', Varien_Data_Collection_Db::SORT_ORDER_DESC)->setPageSize(1)->getFirstItem();
        if (!$lastOrder->getId()) { return false; }
        return $lastOrder->getPayment()->getMethod();
    }    
    
    public function ebizmartsEnabled($paymentMethod)
    {
        return $this->isModuleOutputEnabled('Ebizmarts_SagePaySuite') && Mage::helper('sagepaysuite')->isSagePayMethod($paymentMethod);
    }    
   
    public function pointsUnitName()
    {
        return Mage::helper('points/config')->getPointUnitName();
    }    
    
    public function giftcardEnabled()
    {
        if ($this->isModuleEnabled('Enterprise_GiftCardAccount')) { return true; }
        return false;
    }    
    
    public function urlDel($itemToDestroy)
    {
        $olegnaxOscIndexPageUrl = Mage::getUrl('olegnax_osc/index/index');
        $olegnaxOscEncodedPageUrl = Mage::helper('core/url')->getEncodedUrl($olegnaxOscIndexPageUrl);
        return Mage::getModel('core/url')->getUrl('checkout/cart/delete', array('id'=>$itemToDestroy->getId(), Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $olegnaxOscEncodedPageUrl));
    }
    
    public function creditEnabled()
    {
        if ($this->isModuleEnabled('Enterprise_CustomerBalance')) { if (Mage::helper('enterprise_customerbalance')->isEnabled()) { return true; }}
        return false;
    }    
    
    protected function creditBlock()
    {
        if (!$this->blockCredits) { $this->blockCredits = Mage::app()->getLayout()->createBlock('enterprise_customerbalance/checkout_onepage_payment_additional'); }
        return $this->blockCredits;
    }    
    
    public function creditAvailable() { return $this->creditBlock()->isAllowed(); }    
    
    public function balanceUsed() { return $this->creditBlock()->isCustomerBalanceUsed(); }    
    
    public function getBalance() { return $this->creditBlock()->getBalance(); }    
    
    public function isMageNewsletterEnabled() { return $this->isModuleOutputEnabled('Mage_Newsletter'); }    
    
    public function subscribeCustomer($data = array()) { Mage::getModel('newsletter/subscriber')->subscribe($data['email']); }    
    
    public function pointsEnabled()
    {
        if ($this->isModuleEnabled('Enterprise_Reward')) { if (Mage::helper('enterprise_reward')->isEnabled()){ return true; } }
        return false;
    }    
   
    protected function pointsBlock()
    {
        if (!$this->_pointsBlock) { $this->_pointsBlock = Mage::app()->getLayout()->createBlock('enterprise_reward/checkout_payment_additional'); }
        return $this->_pointsBlock;
    }
    
    public function pointsAvailable() { return $this->pointsBlock()->getCanUseRewardPoints(); }
    
    public function pointsUnit() { return $this->__('Reward points'); }
    
    public function customerSummary() { return $this->pointsBlock()->getPointsBalance(); }
    
    public function pointsToMoney() { return $this->pointsBlock()->getCurrencyAmount(); }    
    
    public function doRewardForPoints() { return $this->pointsBlock()->useRewardPoints(); } 
}
?>
