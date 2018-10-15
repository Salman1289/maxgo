<?php

function removeDir($dirName) 
{ 
	if ( !file_exists($dirName) )return; 
	if ( !is_dir($dirName) ) { 
		unlink ($dirName); 
		return;
	}
	
	$d = dir($dirName); 
	while($entry = $d->read()) { 
		if ($entry != "." && $entry != "..") { 
			if (is_dir($dirName."/".$entry)) { 
				removeDir($dirName."/".$entry); 
			} else { 
				unlink ($dirName."/".$entry); 
			} 
		} 
	} 
	$d->close(); 
	rmdir($dirName); 
}


$files = array(
	'app' => array(
		'app/code/community/Inchoo/Prevnext/',

		'app/code/local/Olegnax/Athlete/',
		'app/code/local/Olegnax/Athleteslideshow/',
		'app/code/local/Olegnax/Ajaxcart/',
		'app/code/local/Olegnax/All/',
		'app/code/local/Olegnax/Colorswatches/',
		'app/code/local/Olegnax/Megamenu/',

		'app/design/adminhtml/default/default/layout/olegnax/athlete.xml',

		'app/design/frontend/athlete/',

		'app/design/frontend/base/default/layout/olegnax/ajaxcart.xml',
		'app/design/frontend/base/default/layout/olegnax/megamenu.xml',
		'app/design/frontend/base/default/layout/olegnax/colorswatches.xml',
		'app/design/frontend/base/default/template/olegnax/ajaxcart',
		'app/design/frontend/base/default/template/olegnax/megamenu',
		'app/design/frontend/base/default/template/olegnax/colorswatches',

		'app/etc/modules/Inchoo_Prevnext.xml',
		'app/etc/modules/Olegnax_Athlete.xml',
		'app/etc/modules/Olegnax_Athleteslideshow.xml',
		'app/etc/modules/Olegnax_Ajaxcart.xml',
		'app/etc/modules/Olegnax_All.xml',
		'app/etc/modules/Olegnax_Colorswatches.xml',
		'app/etc/modules/Olegnax_Megamenu.xml',

		'app/locale/en_US/Inchoo_Prevnext.csv',
		'app/locale/en_US/Olegnax_Ajaxcart.csv',
		'app/locale/en_US/Olegnax_Megamenu.csv',
		'app/locale/en_US/Olegnax_Colorswatches.csv',
	),
	'media' => array(
		'media/olegnax/athlete/',
		'wysiwyg/olegnax/athlete/',
		'wysiwyg/olegnax/colorswatches/',
	),
	'skin' => array(
		'skin/frontend/athlete/',
		'skin/frontend/base/default/css/olegnax/',
		'skin/frontend/base/default/js/olegnax/',
		'athlete_changelog.txt',
	),
);

echo 'Olegnax Athlete uninstaller'."<br/>";
echo '---------------------------'."<br/>";

if (file_exists('./app/etc/local.xml')) {

	$xml = simplexml_load_file('./app/etc/local.xml');

	$table_prefix = $xml->global->resources->db->table_prefix;
	$dbhost = $xml->global->resources->default_setup->connection->host;
	$dbuser = $xml->global->resources->default_setup->connection->username;
	$dbpass = $xml->global->resources->default_setup->connection->password;
	$dbname = $xml->global->resources->default_setup->connection->dbname;
	
	$conn = mysql_connect($dbhost,$dbuser,$dbpass);
	@mysql_select_db($dbname) or die("Unable to select database");
	
} else {
	die("Unable to connect to database");
}


echo 'removing extensions files'."<br/>";
foreach ( $files['app'] as $f ) {
	removeDir($f);
}

echo 'removing media files'."<br/>";
foreach ( $files['media'] as $f ) {
	removeDir($f);
}

echo 'removing skin files'."<br/>";
foreach ( $files['skin'] as $f ) {
	removeDir($f);
}


$queries = array(
	//slideshow
	"DROP TABLE IF EXISTS `{$table_prefix}athlete_slides_store`",
	"DROP TABLE IF EXISTS `{$table_prefix}athlete_slides`",
	"DROP TABLE IF EXISTS `{$table_prefix}athlete_revolution_slides_store`",
	"DROP TABLE IF EXISTS `{$table_prefix}athlete_revolution_slides`",
	//athlete
	"DROP TABLE IF EXISTS `{$table_prefix}athlete_bannerslider_slides_store`",
	"DROP TABLE IF EXISTS `{$table_prefix}athlete_bannerslider_slides`",	
	"DROP TABLE IF EXISTS `{$table_prefix}athlete_bannerslider_slides_group`",
	//megamenu
	"DELETE from `{$table_prefix}eav_attribute` where attribute_code like 'olegnaxmegamenu_%'",
	//clear resource table
	"DELETE from `{$table_prefix}core_resource` where code = 'athlete_setup'",
	"DELETE from `{$table_prefix}core_resource` where code = 'athleteslideshow_setup'",
	"DELETE from `{$table_prefix}core_resource` where code = 'olegnaxall_setup'",
	"DELETE from `{$table_prefix}core_resource` where code = 'olegnaxmegamenu_setup'",
);

/*
TODO: clear configuration ??
athlete_appearance/slideshow/bg_nav
athlete/header/navigation
athlete_banners/header/all
athleteslideshow/config/enabled
olegnaxmegamenu/general/status
olegnaxcolorswatches/main/status
oxajax/general/status
athlete_brands/slider/brand_scroll_items
*/

echo 'clean up database'."<br/>";
foreach ($queries as $_query) {
	mysql_query($_query) or die($_query.': '.mysql_error());
}
//TODO
//echo 'set default design package';
//echo 'clean cache ';
//removeDir($f);
echo 'done'."<br/>";