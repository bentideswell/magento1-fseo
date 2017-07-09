<?php
/**
 * @category    Fishpig
 * @package     Fishpig_AttributeSplash
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_FSeo_Block_Adminhtml_Dashboard extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct()
	{
		parent::__construct();

		$this->setId('fseo_layer_tabs');
        $this->setDestElementId('fseo_dashboard_tab_content');
		$this->setTitle($this->__('SEO'));
		$this->setTemplate('widget/tabshoriz.phtml');
	}
	
	protected function _prepareLayout()
	{
		$tabs = array(
			'adminhtml_catalog_layer_page' => 'Layered Nav',
		);
		
		$_layout = $this->getLayout();
		
		foreach($tabs as $alias => $label) {
			$this->addTab($alias, array(
				'label'     => Mage::helper('catalog')->__($label),
				'content'   => $_layout->createBlock('fseo/' . $alias)->toHtml(),
			));
		}
				
		return parent::_prepareLayout();
	}
}
