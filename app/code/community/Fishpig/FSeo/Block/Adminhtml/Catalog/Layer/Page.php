<?php
/**
 * @category    Fishpig
 * @package     Fishpig_FSeo
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_FSeo_Block_Adminhtml_Catalog_Layer_Page extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{	
		parent::__construct();
		
		$this->_controller = 'adminhtml_catalog_layer_page';
		$this->_blockGroup = 'fseo';
		$this->_headerText = $this->__('Layered Navigation');

		$this->_removeButton('add');
	}
}