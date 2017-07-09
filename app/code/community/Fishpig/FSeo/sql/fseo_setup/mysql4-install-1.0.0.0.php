<?php
/**
 * @category    Fishpig
 * @package    Fishpig_FSeo
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

	$this->startSetup();

	$this->run("

		CREATE TABLE IF NOT EXISTS {$this->getTable('fseo_layer_page')} (
			`page_id` int(11) unsigned NOT NULL auto_increment,
			`type_id` int(1) unsigned NOT NULL default 1,
			`page_name` varchar(255) NOT NULL default '',
			`target_object_url_key` varchar(255) default NULL,
			`target_filter_url_key` varchar(255) default NULL,
			`values` TEXT default NULL,
			`store_ids` varchar(255) NOT NULL default 0,
			`is_enabled` int(1) unsigned NOT NULL default 1,
			`created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
			`updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY (`page_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='FSeo: Pages for Layered Navigation';

	");

	$this->endSetup();
