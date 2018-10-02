<?php

class Olegnax_Osc_Helper_Config extends Mage_Core_Helper_Abstract
{ 
    const OSC_BLOCKS_ORDERING = 'olegnax_osc/block_ordering/';    
    const OSC_SKIN = 'olegnax_appearance/skin/skin';    
    const OSC_CUSTOM_CSS = 'olegnax_appearance/css/rules';
    const OLEGNAX_OSC_IS_ENABLED = 'olegnax_osc/general/is_enabled';    
    const USE_BILLING_AS_SHIPPING = 'olegnax_osc/general/is_use_billing_as_shipping';    
    const OSC_PAGE_TITLE = 'olegnax_osc/general/title';    
    const OSC_DESCRIPTION = 'olegnax_osc/general/description';        
    const OSC_IS_CART_EDITABLE = 'olegnax_osc/general/is_cart_editable';    
    const OSC_INCLUDE_IS_COMPANY = 'olegnax_osc/exclude_include_fields/is_company';    
    const OSC_INCLUDE_IS_FAX = 'olegnax_osc/exclude_include_fields/is_fax';
    const OSC_IS_BLOCK_NUMBERING = 'olegnax_osc/general/is_block_numbering';    
    const OSC_DEFAULT_PAYMENT_METHOD = 'olegnax_osc/general/default_payment_method';    
    const OSC_DEFAULT_SHIPPING_METHOD = 'olegnax_osc/general/default_shipping_method';
    const OSC_INCLUDE_IS_RELATED_PRODUCTS = 'olegnax_osc/exclude_include_fields/is_related_products';   
    const OSC_INCLUDE_AUTORELATED_RULE_ID = 'olegnax_osc/exclude_include_fields/autorelated_rule_id';   
    const OSC_DISPLAY_APPLY_COUPON_BUTTON = 'olegnax_osc/general/display_apply_coupon_button';    
    const OSC_ENABLE_RESPONSIVE = 'olegnax_osc/frontend/enable_responsive';        
    const OSC_COLUMNS_COUNT = 'olegnax_osc/frontend/columns_count';   
    const OSC_INCLUDE_IS_COUPON = 'olegnax_osc/exclude_include_fields/is_coupon';    
    const OSC_INCLUDE_IS_COMMENTS = 'olegnax_osc/exclude_include_fields/is_comments';   
    const OSC_INCLUDE_IS_NEWSLETTER = 'olegnax_osc/exclude_include_fields/is_newsletter';    
    const OSC_LAYOUT_PREFIX = 'olegnax_osc/frontend/layout_';    
    const OSC_REDIRECT_TO = 'olegnax_osc/general/redirect_to';    
    const OSC_BLOCKS_COLUMN = 'olegnax_osc/block_columns/';    
    
   
    public function sectionReset($section)
    {
        if ( ! in_array($section, array('olegnax_osc', 'olegnax_appearance'))) { Mage::throwException($this->__('Unknown section')); }   
        $moduleConfig = Mage::getConfig()->loadModulesConfiguration('system.xml')->getNode();
        if ( ! isset($moduleConfig->sections->$section)) { Mage::throwException($this->__('Unknown section')); }        
        $moduleSetup = Mage::getConfig()->getResourceModelInstance('core/setup');
        $className = get_class($moduleSetup);
        $moduleSetup = new $className('core_setup');
        foreach ($moduleConfig->sections->$section->groups->children() as $group)
        {            
            foreach ($group->fields->children() as $field)
            {
                $moduleSetup->deleteConfigData($section . '/' . $group->getName() . '/' . $field->getName());
            }
        }
        Mage::app()->getCacheInstance()->cleanType('config');
    }    
   
    public function isAllStuffEnabled($store = null)
    {        
        if($this->isModuleOutputEnabled() && $this->isModuleEnabled() && Mage::getStoreConfig(self::OLEGNAX_OSC_IS_ENABLED, $store)) { return true; }
        return false;
    }    
    
    
    public function isCouponsButtonEnabled($store = null) { return Mage::getStoreConfig(self::OSC_DISPLAY_APPLY_COUPON_BUTTON, $store); }
    
    public function getCurrentSkin($store = null) { return Mage::getStoreConfig(self::OSC_SKIN, $store); }
   
    public function getColumn($block, $store = null) { return (int) Mage::getStoreConfig(self::OSC_BLOCKS_COLUMN . $block, $store); }
    
    public function billingForShipping($store = null) { return Mage::getStoreConfig(self::USE_BILLING_AS_SHIPPING, $store); }
    
    public function titleCheckout($store = null) { return Mage::getStoreConfig(self::OSC_PAGE_TITLE, $store); }
    
    public function descriptionCheckout($store = null) { return Mage::getStoreConfig(self::OSC_DESCRIPTION, $store); }
    
    public function enabledRedirectTo($store = null) { return Mage::getStoreConfig(self::OSC_REDIRECT_TO, $store); }
    
    public function enabledResponsive($store = null) { return Mage::getStoreConfig(self::OSC_ENABLE_RESPONSIVE, $store); }
    
    public function countColumns($store = null) { return Mage::getStoreConfig(self::OSC_COLUMNS_COUNT, $store); }
    
    public function shippingMethod($store = null) { return Mage::getStoreConfig(self::OSC_DEFAULT_SHIPPING_METHOD, $store); }
    
    public function isCoupon($store = null) { return Mage::getStoreConfig(self::OSC_INCLUDE_IS_COUPON, $store); }
    
    public function isCommments($store = null) { return Mage::getStoreConfig(self::OSC_INCLUDE_IS_COMMENTS, $store); }
    
    public function getLayout($store = null) { $columns = (int) $this->countColumns(); if ( ! $columns) $columns = 3; return Mage::getStoreConfig(self::OSC_LAYOUT_PREFIX . $columns, $store); }
    
    public function numberOfBlocks($store = null) { return Mage::getStoreConfig(self::OSC_IS_BLOCK_NUMBERING, $store); }
    
    public function paymentMethod($store = null) { return Mage::getStoreConfig(self::OSC_DEFAULT_PAYMENT_METHOD, $store); }
    
    public function isNewsletter($store = null) { return Mage::getStoreConfig(self::OSC_INCLUDE_IS_NEWSLETTER, $store); }
    
    public function relatedProducts($store = null) { return Mage::getStoreConfig(self::OSC_INCLUDE_IS_RELATED_PRODUCTS, $store); }    
    
    public function editableCart($store = null) { return Mage::getStoreConfig(self::OSC_IS_CART_EDITABLE, $store); }
   
    public function isCompany($store = null) { return Mage::getStoreConfig(self::OSC_INCLUDE_IS_COMPANY, $store); }
   
    public function isFax($store = null) { return Mage::getStoreConfig(self::OSC_INCLUDE_IS_FAX, $store); }
    
    public function orderBlocks($block, $store = null) { return (int) Mage::getStoreConfig(self::OSC_BLOCKS_ORDERING . $block, $store); }
    
    public function cssCustom($store = null) { return Mage::getStoreConfig(self::OSC_CUSTOM_CSS, $store); }    
}