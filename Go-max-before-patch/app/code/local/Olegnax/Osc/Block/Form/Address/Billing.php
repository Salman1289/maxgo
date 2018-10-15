<?php

class Olegnax_Osc_Block_Form_Address_Billing extends Mage_Checkout_Block_Onepage_Billing
{
    protected $taxvat;
    protected $_attributeValidationClasses = array('company'  => '', 'fax' => '', 'telephone' => '', 'region' => '', 'postcode'  => '', 'city' => '', 'street' => ''); 
    
    public function vatVisible()
    {        
        if (method_exists(Mage::helper('customer/address'), 'isVatAttributeVisible')) { return Mage::helper('customer/address')->isVatAttributeVisible(); }
        return false;
    }    
    
    public function taxvatEnabled()
    {
        return $this->getCustomerWidgetTaxvat()->isEnabled();
    }
    
    public function addressSelect($type)
    {
        if ($this->isCustomerLoggedIn()) 
        {
            $customerOptions = array();
            foreach ($this->getCustomer()->getAddresses() as $address) 
            {
                $customerOptions[] = array('label' => $address->format('oneline'), 'value' => $address->getId());
            }
            $details = Mage::getSingleton('checkout/session')->getData('olegnax_osc_form_values');
            if (!isset($details[$type.'_address_id'])) 
            {
                $addressId = $this->getQuote()->getBillingAddress()->getCustomerAddressId();
            }
            else 
            {
                if (empty($details[$type.'_address_id'])) { $addressId = 0; }
                else { $addressId = $details[$type.'_address_id']; }                
            }
            if ($addressId !== 0 && empty($addressId))
            {                
                if ($address = $this->getCustomer()->getPrimaryBillingAddress()) { $addressId = $address->getId(); }
            }
            $selectQuery = $this->getLayout()->createBlock('core/html_select')->setName($type.'_address_id')->setId($type.'-address-select')->setClass('address-select')->setValue($addressId)->setOptions($customerOptions);
            $selectQuery->addOption('', Mage::helper('checkout')->__('New Address'));
            return $selectQuery->getHtml();
        }
        return '';
    }    
    
    public function validationAttributeReq($attributeCode)
    {        
        if (Mage::getStoreConfig('olegnax_osc/required_fields/'.$attributeCode)) { return true; } 
        else { return false; }
    } 
    
    public function widgetName()
    {
        return $this->getLayout()->createBlock('customer/widget_name')->setObject($this->nameWidgetObject())->setForceUseCustomerRequiredAttributes(!$this->isCustomerLoggedIn())->setFieldIdFormat('billing:%s')->setFieldNameFormat('billing[%s]');
    }    
   
    public function dateOfBirthWidget()
    {
        return $this->getLayout()->createBlock('customer/widget_dob')->setDate($this->_dateOfBirthWidget())->setFieldIdFormat('billing:%s')->setFieldNameFormat('billing[%s]');
    }    
    
    public function validationAttributeClass($attributeCode)
    {        
        if (Mage::getStoreConfig('olegnax_osc/required_fields/'.$attributeCode)) { return 'required-entry'; }
        else { return ''; }
        return '';
    }    
    
    public function genderWidget()
    {
        return $this->getLayout()->createBlock('customer/widget_gender')->setGender($this->sessionData('gender'))->setFieldIdFormat('billing:%s')->setFieldNameFormat('billing[%s]');
    }    
    
    public function getEnterpriseCustomerAttributesHtml()
    {
        if (Mage::helper('core')->isModuleEnabled('Enterprise_Customer')) 
        {
            $customerAttributes =  Mage::app()->getLayout()->createBlock('enterprise_customer/form')
                ->addRenderer('image', 'enterprise_customer/form_renderer_image', 'customer/form/renderer/image.phtml')
                ->addRenderer('date', 'enterprise_customer/form_renderer_date', 'customer/form/renderer/date.phtml')
                ->addRenderer('multiline', 'enterprise_customer/form_renderer_multiline', 'customer/form/renderer/multiline.phtml')
                ->setTemplate('olegnax_osc/customer/form/attributes.phtml')
                ->setFormCode('checkout_register')
                ->addRenderer('text', 'enterprise_customer/form_renderer_text', 'customer/form/renderer/text.phtml')
                ->addRenderer('textarea', 'enterprise_customer/form_renderer_textarea', 'customer/form/renderer/textarea.phtml')                
                ->addRenderer('select', 'enterprise_customer/form_renderer_select', 'customer/form/renderer/select.phtml')
                ->addRenderer('multiselect', 'enterprise_customer/form_renderer_multiselect', 'customer/form/renderer/multiselect.phtml')
                ->addRenderer('boolean', 'enterprise_customer/form_renderer_boolean', 'customer/form/renderer/boolean.phtml')
                ->addRenderer('file', 'enterprise_customer/form_renderer_file', 'customer/form/renderer/file.phtml');
            if ($customerAttributes) 
            {                
                $customerAttributes->setEntity(new Mage_Customer_Model_Customer(Mage::getSingleton('checkout/session')->getData('olegnax_osc_form_values/billing')));
                $customerAttributes->setEntityModelClass('customer/customer')->setFieldIdFormat('billing:%1$s');
                $customerAttributes->setFieldNameFormat('billing[%1$s]')->setShowContainer(false);
                return $customerAttributes->setExcludeFileAttributes(true)->toHtml();
            }
        }
        return '';
    }
   
    public function countrySelect($type)
    {        
        $countryValue = Mage::getSingleton('checkout/session')->getData('olegnax_osc_form_values/billing');
        if(!$countryValue['country_id'])
        {            
                 $countryValue = array('country_id' => Mage::getStoreConfig('olegnax_osc/address_settings/country_id'));
                if(!$countryValue['country_id'])
                {
                    $countryValue = array('country_id' => Mage::getStoreConfig('general/country/default'));
                }
        }        
        $selectQuery = $this->getLayout()->createBlock('core/html_select')->setName($type.'[country_id]')->setId($type.':country_id')->setTitle($this->__('Country'))->setValue($countryValue['country_id'])->setOptions($this->getCountryOptions());
        //if ($this->validationAttributeReq('country')) { $selectQuery->setClass('validate-select'); }
        $selectQuery->setClass('validate-select');
        return $selectQuery->getHtml();
    }
   
    public function taxvatWidget()
    {
        return $this->getCustomerWidgetTaxvat()->setTaxvat($this->sessionData('taxvat'))->setFieldIdFormat('billing:%s')->setFieldNameFormat('billing[%s]')->toHtml();     
    }
    
    protected function _dateOfBirthWidget()
    {
        $formValues = Mage::getSingleton('checkout/session')->getData('olegnax_osc_form_values');
        if (isset($formValues['billing']))
        {
            $billingData = $formValues['billing'];
            if (!empty($billingData['year']) && !empty($billingData['month']) && !empty($billingData['day']))
            {
                $zDate = new Zend_Date(array('year'=>$billingData['year'], 'month'=>$billingData['month'], 'day'=>$billingData['day']));
                return $zDate->toString();
            }
        }
        return '';
    }
   
    protected function getCustomerWidgetTaxvat()
    {
        if (!$this->_taxvat) { $this->_taxvat = $this->getLayout()->createBlock('customer/widget_taxvat'); }
        return $this->_taxvat;
    }    
    
    public function getEnterpriseAddressAttributesHtml()
    {
        if (Mage::helper('core')->isModuleEnabled('Enterprise_Customer')) 
        {
            $addressAttributes =  Mage::app()->getLayout()
                ->createBlock('enterprise_customer/form')
                ->setTemplate('olegnax_osc/customer/form/attributes.phtml')
                ->setFormCode('customer_register_address')
                ->addRenderer('file', 'enterprise_customer/form_renderer_file', 'customer/form/renderer/file.phtml')
                ->addRenderer('text', 'enterprise_customer/form_renderer_text', 'customer/form/renderer/text.phtml')
                ->addRenderer('select', 'enterprise_customer/form_renderer_select', 'customer/form/renderer/select.phtml')                
                ->addRenderer('date', 'enterprise_customer/form_renderer_date', 'customer/form/renderer/date.phtml')
                ->addRenderer('multiline', 'enterprise_customer/form_renderer_multiline', 'customer/form/renderer/multiline.phtml')
                ->addRenderer('multiselect', 'enterprise_customer/form_renderer_multiselect', 'customer/form/renderer/multiselect.phtml')
                ->addRenderer('boolean', 'enterprise_customer/form_renderer_boolean', 'customer/form/renderer/boolean.phtml')
                ->addRenderer('textarea', 'enterprise_customer/form_renderer_textarea', 'customer/form/renderer/textarea.phtml')
                ->addRenderer('image', 'enterprise_customer/form_renderer_image', 'customer/form/renderer/image.phtml');

            if ($addressAttributes) 
            {                
                $addressAttributes->setEntity(new Mage_Customer_Model_Address(Mage::getSingleton('checkout/session')->getData('olegnax_osc_form_values/billing')));
                $addressAttributes->setFieldIdFormat('billing:%1$s')->setFieldNameFormat('billing[%1$s]');
                return $addressAttributes->setExcludeFileAttributes(true)->setShowContainer(false)->toHtml();
            }
        }
        return '';
    }    
    
    public function billingForShipping()
    {
        return $this->getConfig()->billingForShipping();
    }    
    
    public function getConfig()
    {
        return Mage::helper('olegnax_osc/config');
    } 
    
    public function billingForShippingChecked()
    {
        if ($address = $this->getQuote()->getShippingAddress()) { return $this->getQuote()->getShippingAddress()->getData('same_as_billing'); }
        return false;
    }    
    
    public function sessionData($path)
    {
        $formValues = Mage::getSingleton('checkout/session')->getData('olegnax_osc_form_values/billing');
        $formValues = Mage::helper('olegnax_osc/customeraddress')->getDefault($formValues, $path);
        if (!empty($formValues[$path])) { return $formValues[$path]; }
        return null;
    }    
    
    public function getAddressChangedUrl()
    {
        return Mage::getUrl('onestepcheckout/ajax/addressSave');
    }    
   
    public function registeredReq()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn() || Mage::helper('checkout')->isAllowedGuestCheckout($this->getQuote());
    }
    
    public function canShowCreateAnAccount()
    {
        return (!Mage::getSingleton('customer/session')->isLoggedIn() && Mage::helper('checkout')->isAllowedGuestCheckout($this->getQuote()));
    }
    
    protected function nameWidgetObject()
    {
        $formValues = Mage::getSingleton('checkout/session')->getData('olegnax_osc_form_values');
        $address = Mage::getModel('sales/quote_address');
        if (isset($formValues['billing'])) 
        {
            $address->addData($formValues['billing']);
        }
        if ($address->getFirstname() || $address->getLastname()) 
        {
            return $address;
        }
        return $this->getQuote()->getCustomer();
    } 
}
?>