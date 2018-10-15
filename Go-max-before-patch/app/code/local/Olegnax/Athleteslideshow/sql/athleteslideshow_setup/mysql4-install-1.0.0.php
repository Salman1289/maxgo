<?php
/**
 * @version   1.0 06.08.2012
 * @author    Olegnax http://www.olegnax.com <mail@olegnax.com>
 * @copyright Copyright (C) 2010 - 2012 Olegnax
 */

$installer = $this;
$installer->startSetup();
$installer->run("

DROP TABLE IF EXISTS `{$this->getTable('athleteslideshow/slides')}`;
CREATE TABLE `{$this->getTable('athleteslideshow/slides')}` (
  `slide_id` int(11) unsigned NOT NULL auto_increment,
  `image` varchar(255) NOT NULL default '',
  `title_color` varchar(255) NOT NULL default '',
  `title_bg` varchar(255) NOT NULL default '',
  `title` text NOT NULL default '',
  `link_color` varchar(255) NOT NULL default '',
  `link_bg` varchar(255) NOT NULL default '',
  `link_hover_color` varchar(255) NOT NULL default '',
  `link_hover_bg` varchar(255) NOT NULL default '',
  `link_text` varchar(255) NOT NULL default '',
  `link_href` varchar(255) NOT NULL default '',
  `banner_1_img` varchar(255) NOT NULL default '',
  `banner_1_imgX2` varchar(255) NOT NULL default '',
  `banner_1_href` varchar(255) NOT NULL default '',
  `banner_2_img` varchar(255) NOT NULL default '',
  `banner_2_imgX2` varchar(255) NOT NULL default '',
  `banner_2_href` varchar(255) NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `sort_order` smallint(6) NOT NULL default '0',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`slide_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `{$this->getTable('athleteslideshow/slides')}` (`slide_id`, `image`, `title`, `link_text`, `link_href`, `banner_1_img`, `banner_1_href`, `banner_2_img`, `banner_2_href`, `status`, `sort_order`, `created_time`, `update_time`) VALUES (1, 'olegnax/athlete/slideshow/slide1.png', 'Join the\r\nRevolution', 'shop now', '//athlete.olegnax.com', 'olegnax/athlete/slideshow/slide_banner1.png', '//athlete.olegnax.com','olegnax/athlete/slideshow/slide_banner2.png', '//athlete.olegnax.com', 1, 10, NOW(), NOW() );
INSERT INTO `{$this->getTable('athleteslideshow/slides')}` (`slide_id`, `image`, `title`, `link_text`, `link_href`, `banner_1_img`, `banner_1_href`, `banner_2_img`, `banner_2_href`, `status`, `sort_order`, `created_time`, `update_time`) VALUES (2, 'olegnax/athlete/slideshow/slide2.jpg', 'Lorem ipsum\r\ndolor sit amen', 'shop now', '//athlete.olegnax.com', 'olegnax/athlete/slideshow/slide_banner1.png', '//athlete.olegnax.com','olegnax/athlete/slideshow/slide_banner2.png', '//athlete.olegnax.com', 1, 10, NOW(), NOW() );
INSERT INTO `{$this->getTable('athleteslideshow/slides')}` (`slide_id`, `image`, `title`, `link_text`, `link_href`, `banner_1_img`, `banner_1_href`, `banner_2_img`, `banner_2_href`, `status`, `sort_order`, `created_time`, `update_time`) VALUES (3, 'olegnax/athlete/slideshow/slide3.jpg', 'due tocse\r\nentel lerge', 'shop now', '//athlete.olegnax.com', 'olegnax/athlete/slideshow/slide_banner1.png', '//athlete.olegnax.com','olegnax/athlete/slideshow/slide_banner2.png', '//athlete.olegnax.com', 1, 10, NOW(), NOW() );

");

/**
 * Drop 'slides_store' table
 */
$conn = $installer->getConnection();
$conn->dropTable($installer->getTable('athleteslideshow/slides_store'));

/**
 * Create table for stores
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('athleteslideshow/slides_store'))
    ->addColumn('slide_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
    'nullable'  => false,
    'primary'   => true,
), 'Slide ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Store ID')
    ->addIndex($installer->getIdxName('athleteslideshow/slides_store', array('store_id')),
    array('store_id'))
    ->addForeignKey($installer->getFkName('athleteslideshow/slides_store', 'slide_id', 'athleteslideshow/slides', 'slide_id'),
    'slide_id', $installer->getTable('athleteslideshow/slides'), 'slide_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('athleteslideshow/slides_store', 'store_id', 'core/store', 'store_id'),
    'store_id', $installer->getTable('core/store'), 'store_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Slide To Store Linkage Table');
$installer->getConnection()->createTable($table);

/**
 * Assign 'all store views' to existing slides
 */
$installer->run("INSERT INTO {$this->getTable('athleteslideshow/slides_store')} (`slide_id`, `store_id`) SELECT `slide_id`, 0 FROM {$this->getTable('athleteslideshow/slides')};");


/**
 * Drop 'slides_store' table
 */
$conn = $installer->getConnection();
/**
 * Assign 'all store views' to existing slides
 */

$installer->endSetup();