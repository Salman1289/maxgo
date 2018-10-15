<?php

class Olegnax_Osc_Helper_Shipping extends Mage_Core_Helper_Data
{    
    public function setShippingMethod()
    {
        if (!Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getShippingMethod())
        {
            $rates = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->collectShippingRates()->save()->getGroupedAllShippingRates();
            if ((count($rates) == 1)) 
            {
                $currentRate = current($rates);
                if (count($currentRate) == 1)
                {                    
                    $shippingMethod = current($currentRate)->getCode();
                }
            } 
            elseif ($lastMethod = $this->lastMethod()) { $shippingMethod = $lastMethod; } 
            elseif ($defaultMethod = Mage::helper('olegnax_osc/config')->shippingMethod()) { $shippingMethod = $defaultMethod; }
            if (isset($shippingMethod)) { Mage::getSingleton('checkout/type_onepage')->saveShippingMethod($shippingMethod); }
        }
    }    
    
    protected function lastMethod()
    {
        $currentCustomer = Mage::getSingleton('customer/session')->getCustomer();
        if (!$currentCustomer->getId()) { return false; }
        $order = Mage::getResourceModel('sales/order_collection')->addFilter('customer_id', $currentCustomer->getId())->addFieldToFilter('shipping_method', array('neq' => ''))->addAttributeToSort('created_at', Varien_Data_Collection_Db::SORT_ORDER_DESC)->setPageSize(1)->getFirstItem();
        if (!$order->getId()) { return false; }
        return $order->getShippingMethod();
    }    
}