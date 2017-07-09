<?php
/**
 * @category    Fishpig
 * @package    Fishpig_FSeo
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

	$this->startSetup();
	
	try {
		$this->getConnection()->addColumn($this->getTable('fseo_layer_page'), 'apply_to', " varchar(120) NOT NULL default '' AFTER is_enabled");
	}
	catch (Exception $e) {
		Mage::logException($e);
	}

	$this->endSetup();
