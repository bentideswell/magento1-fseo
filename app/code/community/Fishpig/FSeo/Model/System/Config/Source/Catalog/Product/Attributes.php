<?php
/**
 * @category    Fishpig
 * @package     Fishpig_FSeo
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_FSeo_Model_System_Config_Source_Catalog_Product_Attributes
{
	protected $_options = null;
	
	public function toOptionArray()
	{
		$options = array();
		
		foreach($this->_getOptions() as $value => $label) {
			$options[] = array('value' => $value, 'label' => $label);
		}

		return $options;
	}
	
	protected function _getOptions()
	{
		if (!is_null($this->_options)) {
			return $this->_options;
		}
		
		$this->_options = array();
		
		$attributes = Mage::getResourceModel('catalog/product_attribute_collection')
			->setItemObjectClass('catalog/resource_eav_attribute')
			->addIsFilterableFilter()
			->addStoreLabel(Mage::app()->getStore()->getId())
			->setOrder('position', 'ASC')
			->load();
		
		$helper = Mage::helper('fseo/layer');

		foreach($attributes as $attribute) {
			$options = $attribute->getSource()->getAllOptions(false);

			foreach($options as $option) {
				if ($helper->formatUrlKey($option['label'])) {
					$this->_options[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
					break;
				}
			}
		}

		return $this->_options;
	}
}
