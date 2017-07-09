<?php
/**
 * @category    Fishpig
 * @package    Fishpig_FSeo
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_FSeo_Block_Adminhtml_Catalog_Layer_Page_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	/**
	 * Set the tab block options
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setId('fseo_catalog_layer_page_edit_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle($this->__('FishPig.co.uk <a href="http://fishpig.co.uk/" target="_blank">&raquo;</a>'));
	}
	
	/**
	 * Add tabs to the tabs block
	 *
	 * @return $this
	 */
	protected function _beforeToHtml()
	{
		$layout = $this->getLayout();
		
		$this->addTab('page', array(
			'label' => $this->helper('adminhtml')->__('General'),
			'title' => $this->helper('adminhtml')->__('General'),
			'content' => $layout->createBlock('fseo/adminhtml_catalog_layer_page_edit_tab_general')->toHtml(),
		));
		
		$this->addTab('attributes', array(
			'label' => $this->helper('adminhtml')->__('Attributes'),
			'title' => $this->helper('adminhtml')->__('Attributes'),
			'content' => $layout->createBlock('fseo/adminhtml_catalog_layer_page_edit_tab_attributes')->toHtml(),
		));
		
		/*
		$this->addTab('fixed', array(
			'label' => $this->helper('adminhtml')->__('Target'),
			'title' => $this->helper('adminhtml')->__('Target'),
			'content' => $layout->createBlock('fseo/adminhtml_catalog_layer_page_edit_tab_fixed')->toHtml(),
		));
		*/
		

		if (Mage::registry('splash_page')) {
			$categoryFiltersHtml = $layout->createBlock('splash/adminhtml_page_edit_tab_categoryOperator')->toHtml() . $layout->createBlock('splash/adminhtml_page_edit_tab_categories')->toHtml();
	
			$categoryFiltersHtml = str_replace(Mage::helper('catalog')->__('Product Categories'), Mage::helper('catalog')->__('Category IDs'), $categoryFiltersHtml);
			
			$this->addTab('category_filters', array(
				'label' => $this->helper('adminhtml')->__('Category Filters'),
				'title' => $this->helper('adminhtml')->__('Category Filters'),
				'content' => $categoryFiltersHtml,
			));
		}

		return parent::_beforeToHtml();
	}
}
