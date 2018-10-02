<?php
$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer->startSetup();

$installer->setConfigData('athlete_appearance/header/top_bar_cart_bg', '');
$installer->setConfigData('athlete_appearance/header/top_bar_cart_color', '');
$installer->setConfigData('athlete_appearance/header/top_bar_cart_hover_color', '');
$installer->setConfigData('athlete_appearance/header/top_bar_cart_hover_bg_color', '');
$installer->setConfigData('athlete_appearance/header/search_btn_bg', '');

$installer->setConfigData('athlete/product_info/related_col', '5');
$installer->setConfigData('athlete/product_info/upsell_col', '5');

$installer->setConfigData('athlete_appearance/cart/totals_checkout_icon', 'black');

$installer->endSetup();