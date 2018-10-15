<?php
class Olegnax_Osc_IndexController extends Mage_Checkout_Controller_Action
{   
    public function preDispatch()
    {
        parent::preDispatch();
        $this->_preDispatchValidateCustomer();
        $sessionQuote = Mage::getSingleton('checkout/session')->getQuote();
        if ($sessionQuote->getIsMultiShipping()) 
        {
            $sessionQuote->setIsMultiShipping(false);
            $sessionQuote->removeAllAddresses();
        }
        $workWithGuests = $this->getRequest()->getActionName() == 'index' || Mage::getSingleton('customer/session')->isLoggedIn() || !Mage::helper('olegnax_osc')->loggedReq() ||         $workWithGuests = $this->getRequest()->getActionName() == 'index' || Mage::getSingleton('customer/session')->isLoggedIn() || !Mage::helper('olegnax_osc')->loggedReq() ;
        if(!$workWithGuests)
        {
            $this->norouteAction();
            $this->setFlag('',self::FLAG_NO_DISPATCH,true);
            return;
        }
        return $this;
    }    
   
    public function indexAction()
    {
        if (!Mage::helper('olegnax_osc/config')->isAllStuffEnabled()) 
        {
            Mage::getSingleton('checkout/session')->addError($this->__('The onestep checkout is disabled.'));
            $this->_redirect('checkout/cart');
            return;
        }
        $sessionQuote = Mage::getSingleton('checkout/type_onepage')->getQuote();
        if ($sessionQuote->getHasError() || !$sessionQuote->hasItems()) 
        {
            $this->_redirect('checkout/cart');
            return;
        }
        if (!$sessionQuote->validateMinimumAmount()) 
        {            
            Mage::getSingleton('checkout/session')->addError(Mage::getStoreConfig('sales/minimum_order/error_message'));
            $this->_redirect('checkout/cart');
            return;
        }
        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        Mage::getSingleton('checkout/type_onepage')->initCheckout();        
        $sessionData = Mage::getSingleton('checkout/session')->getData('olegnax_osc_form_values');
        if ($sessionData && array_key_exists('billing', $sessionData)) 
        {
            if (isset($sessionData['billing_address_id'])) 
            {
                Mage::helper('olegnax_osc/customeraddress')->saveBilling($sessionData['billing'], $sessionData['billing_address_id']);
            }
            if (isset($sessionData['billing']['use_for_shipping']) && $sessionData['billing']['use_for_shipping'] == 0 && isset($sessionData['shipping_address_id']))
            {
                Mage::helper('olegnax_osc/customeraddress')->shippingSave($sessionData['shipping'], $sessionData['shipping_address_id']);
            }
        }
        Mage::helper('olegnax_osc/customeraddress')->setAddress();
        Mage::helper('olegnax_osc/shipping')->setShippingMethod();
        Mage::helper('olegnax_osc/payment')->setPaymentMethod();
        $info = array('gw_add_card' => false);
        if (Mage::getSingleton('checkout/type_onepage')->getQuote()->getShippingAddress())
        {
            Mage::getSingleton('checkout/type_onepage')->getQuote()->getShippingAddress()->addData($info);
        }
        Mage::getSingleton('checkout/type_onepage')->getQuote()->addData($info);
        Mage::getSingleton('checkout/type_onepage')->getQuote()->setTotalsCollectedFlag(false);
        Mage::getSingleton('checkout/type_onepage')->getQuote()->collectTotals()->save();
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Checkout'));
        $this->renderLayout();
    }
}
?>