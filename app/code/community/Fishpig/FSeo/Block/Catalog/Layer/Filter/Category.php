<?php
/**
 * @category    Fishpig
 * @package    Fishpig_FSeo
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_FSeo_Block_Catalog_Layer_Filter_Category extends Mage_Catalog_Block_Layer_Filter_Category
{
	public function __construct()
	{
		parent::__construct();
		
		$this->_filterModelName = 'Fishpig_FSeo_Model_Catalog_Layer_Filter_Category';
	}
}
