<?php
class Olegnax_Osc_Block_Form_Address_Shipping extends Mage_Checkout_Block_Onepage_Shipping
{
    protected $_attributeValidationClasses = array('fax'=>'', 'telephone'=>'', 'postcode'  => '', 'city'=> '', 'street'=>'', 'company'=>'', 'region'=>'');
    
    public function addressSelect($type)
    {
        if ($this->isCustomerLoggedIn()) 
        {
            $addressOptions = array();
            foreach ($this->getCustomer()->getAddresses() as $address)
            {
                $addressOptions[] = array('value' => $address->getId(), 'label' => $address->format('oneline'));
            }
            $details = Mage::getSingleton('checkout/session')->getData('olegnax_osc_form_values');
            if (isset($details[$type . '_address_id']))
            {
                if (empty($details[$type . '_address_id'])) 
                {
                    $addressId = 0;
                } 
                else  
                {
                    $addressId = $details[$type . '_address_id'];
                }
            } 
            else 
            {
                $addressId = $this->getQuote()->getBillingAddress()->getCustomerAddressId();
            }
            if ($addressId !== 0 && empty($addressId))
            {
                $address = $this->getCustomer()->getPrimaryBillingAddress();
                if ($address) { $addressId = $address->getId(); }
            }
            $selectQuery = $this->getLayout()->createBlock('core/html_select')->setName($type.'_address_id')->setId($type.'-address-select')->setClass('address-select')->setValue($addressId)->setOptions($addressOptions);
            $selectQuery->addOption('', Mage::helper('checkout')->__('New Address'));
            return $selectQuery->getHtml();
        }
        return '';
    }    
    
    public function widgetName()
    {
        return  $this->getLayout()->createBlock('customer/widget_name')->setObject($this->customerNameWidgetObject())->setFieldIdFormat('shipping:%s')->setFieldNameFormat('shipping[%s]')->setFieldParams('onchange="shipping.setSameAsBilling(false)"');
    }    
    
    public function vatVisible()
    {        
        if (method_exists(Mage::helper('customer/address'), 'isVatAttributeVisible')) { return Mage::helper('customer/address')->isVatAttributeVisible(); }
        return false;
    }    
    
    public function getEnterpriseAddressAttributesHtml()
    {
        if (Mage::helper('core')->isModuleEnabled('Enterprise_Customer')) 
        {
            $attributes =  Mage::app()->getLayout()
                ->createBlock('enterprise_customer/form')
                ->setTemplate('olegnax_osc/customer/form/attributes.phtml')
                ->addRenderer('select', 'enterprise_customer/form_renderer_select', 'customer/form/renderer/select.phtml')
                ->addRenderer('text', 'enterprise_customer/form_renderer_text', 'customer/form/renderer/text.phtml')
                ->addRenderer('textarea', 'enterprise_customer/form_renderer_textarea', 'customer/form/renderer/textarea.phtml')
                ->addRenderer('multiline', 'enterprise_customer/form_renderer_multiline', 'customer/form/renderer/multiline.phtml')
                ->addRenderer('date', 'enterprise_customer/form_renderer_date', 'customer/form/renderer/date.phtml')
                ->setFormCode('customer_register_address')
                ->addRenderer('boolean', 'enterprise_customer/form_renderer_boolean', 'customer/form/renderer/boolean.phtml')
                ->addRenderer('multiselect', 'enterprise_customer/form_renderer_multiselect', 'customer/form/renderer/multiselect.phtml')
                ->addRenderer('image', 'enterprise_customer/form_renderer_image', 'customer/form/renderer/image.phtml')
                ->addRenderer('file', 'enterprise_customer/form_renderer_file', 'customer/form/renderer/file.phtml');
            if ($attributes) 
            {                
                $attributes->setEntity(new Mage_Customer_Model_Address(Mage::getSingleton('checkout/session')->getData('olegnax_osc_form_values/shipping')));
                $attributes->setFieldIdFormat('shipping:%1$s')->setFieldNameFormat('shipping[%1$s]');
                return $attributes->setExcludeFileAttributes(true)->setShowContainer(false)->toHtml();
            }
        }
        return '';
    } 
    
    public function sessionData($path)
    {
        $data = Mage::getSingleton('checkout/session')->getData('olegnax_osc_form_values/shipping');
        $data = Mage::helper('olegnax_osc/customeraddress')->getDefault($data, $path);
        if (!empty($data[$path])) { return $data[$path]; }
        return null;
    }    
    
    public function validationReq($attributeCode)
    {
        if (Mage::getStoreConfig('olegnax_osc/required_fields/'.$attributeCode)) { return true; }
        else { return false; }
    }
    
    public function countrySelect($type)
    {
        $selectQuery = $this->getLayout()->createBlock('core/html_select')->setName($type.'[country_id]')->setId($type.':country_id')->setTitle($this->__('Country'))->setValue($this->sessionData('country_id'))->setOptions($this->getCountryOptions());
        //if ($this->validationReq('country')) { $selectQuery->setClass('validate-select'); }
        $selectQuery->setClass('validate-select');
        return $selectQuery->getHtml();
    }    
    
    public function validationClass($attributeCode)
    { 
        if (Mage::getStoreConfig('olegnax_osc/required_fields/'.$attributeCode)) return 'required-entry'; else return '';
        return '';
    } 
    
    public function getConfig()
    {
        return Mage::helper('olegnax_osc/config');
    }    
    
    public function getAddressChangedUrl()
    {
        return Mage::getUrl('onestepcheckout/ajax/addressSave');
    }    
    
    public function isUseBillingAsShipping()
    {
        return $this->getConfig()->billingForShipping();
    }    
    
    protected function customerNameWidgetObject()
    {
        $address = Mage::getModel('sales/quote_address');
        $data = Mage::getSingleton('checkout/session')->getData('olegnax_osc_form_values');
        if (isset($data['shipping']))
        {
            $address->addData($data['shipping']);
        }
        if ($address->getLastname() || $address->getFirstname())
        {
            return $address;
        }
        return $this->getQuote()->getCustomer();
    }
}
?>