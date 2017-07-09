<?php
/**
 * @category    Fishpig
 * @package     Fishpig_FSeo
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_FSeo_Model_System_Config_Source_Catalog_Layer_Entity
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
		
		$this->_options = array(
			Mage_Catalog_Model_Category::ENTITY => Mage::helper('catalog')->__('Category'),
		);
		
		$_config = Mage::getConfig();
		
		if ((string)$_config->getNode('modules/Fishpig_AttributeSplash/active') === 'true') {
			$this->_options['attributeSplash_page'] = Mage::helper('fseo/layer')->__('Splash Page');
		}
		
		if ((string)$_config->getNode('modules/Fishpig_AttributeSplashPro/active') === 'true') {
			$this->_options['splash_page'] = Mage::helper('fseo/layer')->__('Splash Pro Page');
		}

		return $this->_options;
	}
}
