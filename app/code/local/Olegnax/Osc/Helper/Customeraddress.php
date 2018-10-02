<?php
class Olegnax_Osc_Helper_Customeraddress extends Mage_Core_Helper_Data
{    
    public function shippingSave($data = array(), $customerAddressId = null)
    {
        $shippingAddress = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();
        if (empty($customerAddressId))
        {
            $shippingAddress->addData($data);
        } 
        else 
        {
            $address = Mage::getModel('customer/address')->load($customerAddressId);
            if ($address->getId()) 
            {
                if ($address->getCustomerId() != Mage::getSingleton('checkout/session')->getQuote()->getCustomerId()) 
                {
                    return array('error'   => 1, 'message' => Mage::helper('checkout')->__('Customer Address is not valid.'));
                }
                $shippingAddress->importCustomerAddress($address);
            }            
        }
        $shippingAddress->implodeStreetAddress();
        $shippingAddress->setSameAsBilling(0);
        Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        return array();
    }    
    
    public function setAddress()
    {
        if (Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getCustomerAddressId()) 
        {            
            $this->billingSave(array('use_for_shipping' => true), Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getCustomerAddressId());
            Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->setCollectShippingRates(true);
            Mage::getSingleton('checkout/session')->getQuote()->collectTotals();
            Mage::getSingleton('checkout/session')->getQuote()->save();
            return;
        }        
        $billingAddress = Mage::getSingleton('customer/session')->getCustomer()->getPrimaryBillingAddress();
        if (!$billingAddress)
        {
            if (!is_null(Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getId())) { return; }
            $params = array('country_id'=>Mage::getStoreConfig('general/country/default'), 'use_for_shipping' => true);
            $this->billingSave($params);
        }
        else 
        {
            $this->billingSave(array('use_for_shipping' => true), $billingAddress->getId());
        }
    }    
    
    public function checking(Mage_Customer_Model_Address_Abstract $address)
    {
        if (Mage::getStoreConfig('olegnax_osc/required_fields/country') && !Zend_Validate::is($address->getCountryId(), 'NotEmpty')) { $address->addError(Mage::helper('customer')->__('Please enter the country.')); }
        if (!Zend_Validate::is($address->getFirstname(), 'NotEmpty')) { $address->addError(Mage::helper('customer')->__('Please enter the first name.')); }
        if (!Zend_Validate::is($address->getLastname(), 'NotEmpty')) { $address->addError(Mage::helper('customer')->__('Please enter the last name.')); }
        if (Mage::getStoreConfig('olegnax_osc/required_fields/street') && !Zend_Validate::is($address->getStreet(1), 'NotEmpty')) { $address->addError(Mage::helper('customer')->__('Please enter the street.')); }
        if (Mage::getStoreConfig('olegnax_osc/required_fields/postcode')) 
        {
            $_havingOptionalZip = Mage::helper('directory')->getCountriesWithOptionalZip();
            if (!in_array($address->getCountryId(), $_havingOptionalZip) && !Zend_Validate::is($address->getPostcode(), 'NotEmpty')){ $address->addError(Mage::helper('customer')->__('Please enter the zip/postal code.')); }
        }        
        if (Mage::getStoreConfig('olegnax_osc/required_fields/city') && !Zend_Validate::is($address->getCity(), 'NotEmpty')) { $address->addError(Mage::helper('customer')->__('Please enter the city.'));  }
        if (Mage::getStoreConfig('olegnax_osc/required_fields/telephone') && !Zend_Validate::is($address->getTelephone(), 'NotEmpty')) { $address->addError(Mage::helper('customer')->__('Please enter the telephone number.')); }                
        if ($address->getCountryModel()->getRegionCollection()->getSize() && !Zend_Validate::is($address->getRegionId(), 'NotEmpty') && Mage::helper('directory')->isRegionRequired($address->getCountryId())) { $address->addError(Mage::helper('customer')->__('Please enter the state/province.')); }
    }    

    public function getDefault($data, $way)
    {
        if (empty($data[$way]))
        {
            switch ($way)
            {
                case 'region_id':
                    $data[$way] = Mage::getStoreConfig('olegnax_osc/address_settings/region_id');
                    break;
                case 'country_id':
                    $data[$way] = Mage::getStoreConfig('olegnax_osc/address_settings/country_id');
                    if (empty($data[$way])){ $data[$way] = Mage::getStoreConfig('general/country/default'); }
                case 'street':
                    $data[$way] = array(Mage::getStoreConfig('olegnax_osc/address_settings/street_line1'));
                    break;
                case 'fax':
                case 'postcode':
                case 'city':                
                case 'company':
                case 'telephone':
                    $data[$way] = Mage::getStoreConfig('olegnax_osc/address_settings/' . $way);
                    break;
                case 'region':                    
            }
        }
        return $data;
    }
        
    public function billingSave($data = array(), $customerAddressId = null)
    {
        $billingAddress = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress();
        if (empty($customerAddressId)) 
        {
            if (@class_exists('Mage_Customer_Model_Form')) 
            {
                $billingForm = Mage::getModel('customer/form');
                $billingForm->setFormCode('customer_address_edit')->setEntityType('customer_address')->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());
                $billingForm->setEntity($billingAddress);               
                $billingForm->compactData($billingForm->extractData($billingForm->prepareRequest($data)));             
                foreach ($billingForm->getAttributes() as $attribute) 
                {
                    if (!isset($data[$attribute->getAttributeCode()])) 
                    {
                        $billingAddress->setData($attribute->getAttributeCode(), NULL);
                    }
                }
                $billingAddress->setCustomerAddressId(null);
                if(!empty($data['save_in_address_book']))
                {
                    $billingAddress->setSaveInAddressBook(1);                    
                }
                else
                {
                    $billingAddress->setSaveInAddressBook(0);
                }                
            }
            else 
            {
                $billingAddress->addData($data);
            }
        } 
        else 
        {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) 
            {
                if ($customerAddress->getCustomerId() != Mage::getSingleton('checkout/session')->getQuote()->getCustomerId()) 
                {
                    return array('error'   => 1, 'message' => Mage::helper('checkout')->__('Customer Address is not valid.'));
                }
                $billingAddress->importCustomerAddress($customerAddress);
            }            
        }
        $billingAddress->implodeStreetAddress();
        if (!Mage::getSingleton('checkout/session')->getQuote()->isVirtual()) 
        {
            if (isset($data['use_for_shipping'])) 
            {
                $cas = 1;
            } 
            else 
            {
                $cas = 0;
            }
            switch ($cas) 
            {
                case 0:
                    $shipping = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();
                    $shipping->setSameAsBilling(0);
                    break;
                case 1:
                    $billing = clone $billingAddress;
                    $billing->unsAddressId()->unsAddressType();
                    $shipping = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();
                    $shippingMethod = $shipping->getShippingMethod();
                    $shipping->addData($billing->getData())->setSameAsBilling(1)->setShippingMethod($shippingMethod);
                    break;
            }
            Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        }
        return array();
    }        
}
?>
