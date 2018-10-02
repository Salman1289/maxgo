<?php

class Olegnax_Osc_Helper_Payment extends Mage_Core_Helper_Data
{    
    public function setPaymentMethod()
    {        
        if (!Mage::getSingleton('checkout/session')->getQuote()->getPayment()->getMethod())
        {
            $paymentData = array();
            $methods = Mage::app()->getLayout()->createBlock('olegnax_osc/form_paymentmethod')->getMethods();
            if ((count($methods) == 1)) 
            {                
                $paymentData['method'] = current($methods)->getCode();
            } 
            elseif ($lastMethod = $this->lastMethod()) 
            {
                $paymentData['method'] = $lastMethod;
            } 
            elseif ($defaultMethod = Mage::helper('olegnax_osc/config')->paymentMethod()) 
            {
                $paymentData['method'] = $defaultMethod;
            }
            if (!empty($paymentData)) 
            {
                try { Mage::getSingleton('checkout/type_onepage')->savePayment($paymentData); }
                catch (Exception $e) {}
            }
        }
    }
    
    protected function lastMethod()
    {
        $currentCustomer = Mage::getSingleton('customer/session')->getCustomer();
        if (!$currentCustomer->getId()) { return false; }        
        $order = Mage::getResourceModel('sales/order_collection')->addFilter('customer_id', $currentCustomer->getId())->addAttributeToSort('created_at', Varien_Data_Collection_Db::SORT_ORDER_DESC)->setPageSize(1)->getFirstItem();
        if (!$order->getId()) { return false; }
        return $order->getPayment()->getMethod();
    }
    
    public function isEbizmartsSagePaySuiteMethod($paymentMethod)
    {
        return $this->isModuleOutputEnabled('Ebizmarts_SagePaySuite') && Mage::helper('sagepaysuite')->isSagePayMethod($paymentMethod);
    }
}