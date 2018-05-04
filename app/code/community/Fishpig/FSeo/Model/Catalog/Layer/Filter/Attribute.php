<?php
/**
 * @category    Fishpig
 * @package    Fishpig_FSeo
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_FSeo_Model_Catalog_Layer_Filter_Attribute extends Mage_Catalog_Model_Layer_Filter_Attribute
{
	/**
	 *
	 *
	 * @param Zend_Controller_Request_Abstract $request
	 * @param $filterBlock
	 * @return $this
	 */
	public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
	{
		$filterValues = $request->getParam($this->_requestVar);

		if (!is_array($filterValues)) {
			$filterValues = array($filterValues);
		}
		
		$stateItems = array();
		
		foreach($filterValues as $filterValue) {
			if (($text = trim($this->_getOptionText($filterValue))) !== '') {
				$stateItems[$filterValue] = $text;
			}
		}

		if ($filterValues && $stateItems) {
			$this->_getResource()->applyFilterToCollection($this, $filterValues);
			
			foreach($stateItems as $filterValue => $text) {
				$this->getLayer()->getState()->addFilter($this->_createItem($text, $filterValue));
			}
		}
		
		return $this;
	}
	
	/**
	 *
	 *
	 * @return array|false
	 */
	protected function _getItemsData()
	{
		$data = parent::_getItemsData();
		
		$selectedValues = Mage::app()->getRequest()->getParam($this->_requestVar);
		
		if (!$selectedValues) {
			return $data;
		}

		if (!Mage::helper('fseo/layer')->isMultiselectAllowed()) {
			return array();
		}

		if (!is_array($selectedValues)) {
			$selectedValues = array($selectedValues);
		}

		// Remove selected attribute options
		foreach($data as $key => $value) {
			if (in_array($value['value'], $selectedValues)) {
#				$data[$key]['count'] = 0;
#				unset($data[$key]);
			}
		}
		
		return $data;
	}
}
