<?php

class Olegnax_Osc_Model_Updater
{
    const FULL_ACTION_NAME = 'olegnax_osc_index_index';

    const SHIPPING_BLOCK_NAME = 'olegnax_osc.form.shippingmethod';
    const GIFT_BLOCK_NAME = 'olegnax_osc.form.giftcard';
    const PAYMENT_BLOCK_NAME = 'olegnax_osc.form.paymentmethod';
    const CART_REVIEW_BLOCK_NAME = 'olegnax_osc.form.review.cart';
    const COUPON_REVIEW_BLOCK_NAME = 'olegnax_osc.form.review.coupon';
    const ENTERPRISE_REVIEW_GIFT_BLOCK_NAME = 'olegnax_osc.form.review.enterprise.giftcard';
    const ENTERPRISE_REVIEW_POINTS_BLOCK_NAME = 'olegnax_osc.form.review.enterprise.points';
    const ENTERPRISE_REVIEW_STORECREDIT_BLOCK_NAME = 'olegnax_osc.form.review.enterprise.storecredit';
    const TOPLINK_BLOCK_NAME= 'top.links';
    const HEAD_BLOCK_NAME_MINICART = 'minicart_head';
    const RELATED_BLOCK_NAME = 'olegnax_osc.related';
    const TOTAL_CART_BLOCK_NAME = 'checkout.onepage.review.info.totals';

    protected $map = array(
        'addressSave'=> array(
            'shipping_method'=>self::SHIPPING_BLOCK_NAME,
            'gift_card'=>self::GIFT_BLOCK_NAME,
            'payment_method'=>self::PAYMENT_BLOCK_NAME,
            'review_cart'=>self::CART_REVIEW_BLOCK_NAME,
            'review_coupon'=>self::COUPON_REVIEW_BLOCK_NAME,
            'review_enterprise_giftcard'=>self::ENTERPRISE_REVIEW_GIFT_BLOCK_NAME,
            'review_enterprise_storecredit'=>self::ENTERPRISE_REVIEW_STORECREDIT_BLOCK_NAME,
            'review_enterprise_points'=>self::ENTERPRISE_REVIEW_POINTS_BLOCK_NAME,           
            'review_totals'=>self::TOTAL_CART_BLOCK_NAME),
        'shippingSave'=> array(
            'payment_method'=> self::PAYMENT_BLOCK_NAME,
            'review_cart'=>self::CART_REVIEW_BLOCK_NAME,
            'review_coupon'=>self::COUPON_REVIEW_BLOCK_NAME,
            'review_enterprise_giftcard'=>self::ENTERPRISE_REVIEW_GIFT_BLOCK_NAME,
            'review_enterprise_storecredit'=>self::ENTERPRISE_REVIEW_STORECREDIT_BLOCK_NAME,
            'review_enterprise_points'=>self::ENTERPRISE_REVIEW_POINTS_BLOCK_NAME,            
            'review_totals'=>self::TOTAL_CART_BLOCK_NAME),
        'setPrintedCardForEnterprise'=>array(
            'payment_method'=>self::PAYMENT_BLOCK_NAME,
            'review_cart' => self::CART_REVIEW_BLOCK_NAME,
            'review_coupon' => self::COUPON_REVIEW_BLOCK_NAME,
            'review_enterprise_giftcard' => self::ENTERPRISE_REVIEW_GIFT_BLOCK_NAME,
            'review_enterprise_storecredit' => self::ENTERPRISE_REVIEW_STORECREDIT_BLOCK_NAME,
            'review_enterprise_points' => self::ENTERPRISE_REVIEW_POINTS_BLOCK_NAME,           
            'review_totals' => self::TOTAL_CART_BLOCK_NAME),
        'paymentSave'=> array(
            'review_cart'=> self::CART_REVIEW_BLOCK_NAME,
            'review_coupon'=> self::COUPON_REVIEW_BLOCK_NAME,
            'review_enterprise_giftcard'=> self::ENTERPRISE_REVIEW_GIFT_BLOCK_NAME,
            'review_enterprise_storecredit' => self::ENTERPRISE_REVIEW_STORECREDIT_BLOCK_NAME,
            'review_enterprise_points'=> self::ENTERPRISE_REVIEW_POINTS_BLOCK_NAME,           
            'review_totals' => self::TOTAL_CART_BLOCK_NAME),
        'confirmCoupon' => array(
            'payment_method' => self::PAYMENT_BLOCK_NAME,
            'review_cart'=> self::CART_REVIEW_BLOCK_NAME,
            'review_enterprise_storecredit' => self::ENTERPRISE_REVIEW_STORECREDIT_BLOCK_NAME,
            'review_enterprise_points'=> self::ENTERPRISE_REVIEW_POINTS_BLOCK_NAME,            
            'review_totals' => self::TOTAL_CART_BLOCK_NAME),
        'confirmGiftcardForEnterprise'   => array(
            'payment_method'=> self::PAYMENT_BLOCK_NAME,
            'review_cart' => self::CART_REVIEW_BLOCK_NAME,
            'review_coupon' => self::COUPON_REVIEW_BLOCK_NAME,
            'review_enterprise_storecredit' => self::ENTERPRISE_REVIEW_STORECREDIT_BLOCK_NAME,
            'review_enterprise_points' => self::ENTERPRISE_REVIEW_POINTS_BLOCK_NAME,            
            'review_totals' => self::TOTAL_CART_BLOCK_NAME),
        'dellGiftcardForEnterprise'=> array(
            'payment_method' => self::PAYMENT_BLOCK_NAME,
            'review_cart' => self::CART_REVIEW_BLOCK_NAME,
            'review_coupon' => self::COUPON_REVIEW_BLOCK_NAME,
            'review_enterprise_giftcard'=> self::ENTERPRISE_REVIEW_GIFT_BLOCK_NAME,
            'review_enterprise_storecredit' => self::ENTERPRISE_REVIEW_STORECREDIT_BLOCK_NAME,
            'review_enterprise_points' => self::ENTERPRISE_REVIEW_POINTS_BLOCK_NAME,            
            'review_totals' => self::TOTAL_CART_BLOCK_NAME),
        'confirmStorecreditForEnterprise' => array(
            'payment_method'=> self::PAYMENT_BLOCK_NAME,
            'review_cart' => self::CART_REVIEW_BLOCK_NAME,
            'review_coupon' => self::COUPON_REVIEW_BLOCK_NAME,
            'review_enterprise_giftcard'=> self::ENTERPRISE_REVIEW_GIFT_BLOCK_NAME,
            'review_enterprise_points' => self::ENTERPRISE_REVIEW_POINTS_BLOCK_NAME,            
            'review_totals'  => self::TOTAL_CART_BLOCK_NAME),
        'confirmPointsForEnterprise' => array(
            'payment_method'  => self::PAYMENT_BLOCK_NAME,
            'review_cart' => self::CART_REVIEW_BLOCK_NAME,
            'review_coupon' => self::COUPON_REVIEW_BLOCK_NAME,
            'review_enterprise_giftcard'    => self::ENTERPRISE_REVIEW_GIFT_BLOCK_NAME,
            'review_enterprise_storecredit' => self::ENTERPRISE_REVIEW_STORECREDIT_BLOCK_NAME,             
            'review_totals'=> self::TOTAL_CART_BLOCK_NAME),
        'applyPoints' => array(
            'payment_method' => self::PAYMENT_BLOCK_NAME,
            'review_cart'  => self::CART_REVIEW_BLOCK_NAME,
            'review_coupon'  => self::COUPON_REVIEW_BLOCK_NAME,
            'review_enterprise_giftcard'   => self::ENTERPRISE_REVIEW_GIFT_BLOCK_NAME,
            'review_enterprise_storecredit' => self::ENTERPRISE_REVIEW_STORECREDIT_BLOCK_NAME,
            'review_enterprise_points'  => self::ENTERPRISE_REVIEW_POINTS_BLOCK_NAME,           
            'review_totals'  => self::TOTAL_CART_BLOCK_NAME),       
        'addToWishlist'=> array(
            'top_links'=> self::TOPLINK_BLOCK_NAME,
            'related'=>self::RELATED_BLOCK_NAME),
        'addToCompare' => array(
            'related' => self::RELATED_BLOCK_NAME),
        'updateBlocksAfterACP'  => array(
            'related' => self::RELATED_BLOCK_NAME,
            'shipping_method' => self::SHIPPING_BLOCK_NAME,
            'gift_card' => self::GIFT_BLOCK_NAME,
            'payment_method' => self::PAYMENT_BLOCK_NAME,
            'review_cart' => self::CART_REVIEW_BLOCK_NAME,
            'review_coupon' => self::COUPON_REVIEW_BLOCK_NAME,
            'review_enterprise_giftcard' => self::ENTERPRISE_REVIEW_GIFT_BLOCK_NAME,
            'review_enterprise_storecredit' => self::ENTERPRISE_REVIEW_STORECREDIT_BLOCK_NAME,
            'review_enterprise_points' => self::ENTERPRISE_REVIEW_POINTS_BLOCK_NAME,             
            'review_totals' => self::TOTAL_CART_BLOCK_NAME),
        'cartUpdate' => array(
            'related' => self::RELATED_BLOCK_NAME,
            'shipping_method' => self::SHIPPING_BLOCK_NAME,
            'gift_card' => self::GIFT_BLOCK_NAME,
            'payment_method' => self::PAYMENT_BLOCK_NAME,
            'review_cart' => self::CART_REVIEW_BLOCK_NAME,
            'review_coupon'  => self::COUPON_REVIEW_BLOCK_NAME,
            'review_enterprise_giftcard' => self::ENTERPRISE_REVIEW_GIFT_BLOCK_NAME,
            'review_enterprise_storecredit' => self::ENTERPRISE_REVIEW_STORECREDIT_BLOCK_NAME,
            'review_enterprise_points'  => self::ENTERPRISE_REVIEW_POINTS_BLOCK_NAME,            
            'top_links' => self::TOPLINK_BLOCK_NAME,
            'minicart_head'  => self::HEAD_BLOCK_NAME_MINICART,
            'review_totals'  => self::TOTAL_CART_BLOCK_NAME));
    
    //DONE+
    public function getBlocks($currentLayout = null, $fullActionName = null)
    {
        sleep(5);
        if (is_null($currentLayout)) { $currentLayout = Mage::app()->getLayout(); }
        if (is_null($fullActionName)) { $fullActionName = self::FULL_ACTION_NAME; }
        $this->initLayout($currentLayout, $fullActionName);
        $action = Mage::app()->getRequest()->getActionName();
        if (!array_key_exists($action, $this->map)) { return array(); }
        if (!is_array($this->map[$action])) { return array(); }
        $blocks = array();
        foreach($this->map[$action] as $key => $name) { if ($currentLayout->getBlock($name)) { $blocks[$key] = $currentLayout->getBlock($name)->toHtml(); } }
        return $blocks;
    }   
    
    //DONE+
    protected function initLayout($currentLayout, $fullActionName)
    {        
        $layoutUpdate = $currentLayout->getUpdate();
        $layoutUpdate->addHandle('default');
        $layoutUpdate->addHandle('STORE_'.Mage::app()->getStore()->getCode());
        $layoutUpdate->addHandle('THEME_'.Mage::getSingleton('core/design_package')->getArea().'_'.Mage::getSingleton('core/design_package')->getPackageName().'_'.Mage::getSingleton('core/design_package')->getTheme('layout'));
        $layoutUpdate->addHandle(strtolower($fullActionName));
        Mage::dispatchEvent('controller_action_layout_load_before', array('action' => Mage::app()->getFrontController()->getAction(), 'layout' => $currentLayout));
        $layoutUpdate->load();
        $currentLayout->generateXml();
        $currentLayout->generateBlocks();       
    }
    
    //DONE+
     public function getMap()
    {
        return $this->map;
    }
}