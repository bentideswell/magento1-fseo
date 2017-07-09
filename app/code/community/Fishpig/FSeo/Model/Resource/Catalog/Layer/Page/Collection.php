<?php
/**
 * @category    Fishpig
 * @package    Fishpig_FSeo
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_FSeo_Model_Resource_Catalog_Layer_Page_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
	/**
	 * Initialize the model type
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('fseo/catalog_layer_page');
	}
}
