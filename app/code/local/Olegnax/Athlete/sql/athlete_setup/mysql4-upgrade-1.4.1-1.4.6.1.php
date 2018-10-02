<?php
$adminVersion = Mage::getConfig()->getModuleConfig('Mage_Admin')->version;
if (version_compare($adminVersion, '1.6.1.2', '>=')) 
{
    $blockNames = array(
        'cms/block',
        'catalog/product_list',
		'athlete/bannerslider',
        'athlete/product_list_featured',
		'athlete/product_list_sale',
		'athlete/product_list_new',
		'athlete/product_list_mostviewed',
		'athlete/product_list_bestsellers',
		'newsletter/subscribe',
		'athlete/social_twitter',
        'page/html',          
    );
    $installer = $this;
	$installer->startSetup();
	foreach ($blockNames as $blockName) 
	{
		if (!$installer->getConnection()->fetchOne("select * from {$this->getTable('permission_block')} where `block_name`='$blockName'")) 
		{
			$installer->run("insert  into {$this->getTable('permission_block')} (`block_name`,`is_allowed`) values ('$blockName','1');");
		}
	}
	$installer->endSetup();	
}
?>
