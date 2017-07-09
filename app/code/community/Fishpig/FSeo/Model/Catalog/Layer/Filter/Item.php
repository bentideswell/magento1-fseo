<?php
/**
 * @category    Fishpig
 * @package    Fishpig_FSeo
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_FSeo_Model_Catalog_Layer_Filter_Item extends Mage_Catalog_Model_Layer_Filter_Item
{
	/**
	 * Get the filter URL
	 *
	 * @return string
	 */
	public function getUrl()
	{
		if (!$this->isEnabled()) {
			return parent::getUrl();
		}

		if ($this->isCategoryFilter()) {
			if (Mage::helper('fseo/layer')->getEntityType() === 'catalog_category') {
				return $this->_getCategoryUrl();
			}
			
			return parent::getUrl();
		}
		else if ($this->isPriceFilter()) {
			return parent::getUrl();			
		}
		else if (is_numeric($this->getSlug())) {
			return parent::getUrl();
		}
		else if ($this->_isApplied()) {
			return $this->getRemoveUrl();	
		}

		if (Mage::helper('fseo/layer')->isAttributeEnabled($this->getFilter()->getRequestVar())) {
			return $this->_getAttributeUrl();
		}
		
		return parent::getUrl();
	}
	
	/**
	 * Get the filter URL
	 *
	 * @return string
	 */
	protected function _getAttributeUrl()
	{
		$_helper = Mage::helper('fseo/layer');

		if ($appliedFilters = $_helper->getAppliedFilters()) {
			$url = $_helper->getEntityUrlKey();

			foreach($_helper->getAttributes() as $attribute) {
				if (isset($appliedFilters[$attribute->getAttributeCode()])) {
					$slug = '';

					foreach($attribute->getPrunedOptions() as $option) {
						foreach($appliedFilters[$attribute->getAttributeCode()]->getOptions() as $appliedFilter) {
							if ($option['value'] === $appliedFilter['value'] && $option['value'] !== $this->getValue()) {
								$slug .= $appliedFilter['slug'] . ',';
							}
						}
						
						if ($option['value'] === $this->getValue()) {
							$slug .= $this->getSlug() . ',';
						}
					}
					
					$url .= '/' . rtrim($slug, ',');
				}
				else if ($attribute->getAttributeCode() === $this->getFilter()->getAttributeModel()->getAttributeCode()) {
					$url .= '/' . $this->getSlug();
				}
			}

			$url .= $_helper->getUrlSuffix();
		}
		else {
			$url = $_helper->getEntityUrlKey() . '/' . $this->getSlug() . $_helper->getUrlSuffix();
		}

        return $this->_getUrl($url);
	}

	/**
	 * Get the filter URL
	 *
	 * @return string
	 */
	protected function _getCategoryUrl()
	{
		$category = Mage::getModel('catalog/category')->load($this->getValue());

		if ($category->hasCustomUrl()) {
			return $category->getCustomUrl();
		}

		$_helper = Mage::helper('fseo/layer');
		$urlKey = rtrim($_helper->getEntityUrlKey() . '/' . $category->getUrlKey() . '/' . $_helper->getFilterUrlKey(), '/') . $_helper->getUrlSuffix();

		return $this->_getUrl($urlKey);
	}

	/**
	 * Get the URL of the current page minus this filter
	 *
	 * @return string
	 */
	public function getRemoveUrl()
	{
		if (!$this->isEnabled()) {
			return parent::getRemoveUrl();
		}

		$_helper = Mage::helper('fseo/layer');

		if ($this->isCategoryFilter()) {
			$categoryTree = array_reverse(Mage::helper('fseo/layer')->getEntityTree());
			$entityUrlKey = '';
			
			foreach($categoryTree as $category) {
				if ($category->getId() === $this->getValue()) {
					break;
				}
				
				$entityUrlKey .= $category->getUrlKey() . '/';
			}

			return $this->_getUrl(trim($entityUrlKey . trim($_helper->getFilterUrlKey(), '/'), '/') . $_helper->getUrlSuffix());
		}
		
		$valueIsArray = is_array($this->getValue());
		$url = $_helper->getEntityUrlKey();

		if ($appliedFilters = $_helper->getAppliedFilters()) {
			foreach($appliedFilters as $appliedFilter) {
				$slug = '';

				foreach($appliedFilter->getOptions() as $option) {
					if ($valueIsArray && !in_array($option['value'], $this->getValue())) {
						$slug .= $option['slug'] . ',';	
					}
					else if (!$valueIsArray && (int)$option['value'] !== (int)$this->getValue()) {
						$slug .= $option['slug'] . ',';
					}
				}
				
				$url = rtrim($url, '/') . '/' . rtrim($slug, ',');
			}
		}
		
		$url = rtrim($url, '/') .$_helper->getUrlSuffix();

		return $this->_getUrl($url);
	}
	
	/**
	 * Get a URL
	 *
	 * @param string $uri
	 * @return string
	 */
	protected function _getUrl($uri)
	{
		return Mage::helper('fseo/layer')->getUrl($uri);
	}

	/**
	 * Get the option value and convert into into a slug (url_key)
	 *
	 * @return string
	 */	
	protected function getSlug()
	{
		if ($this->isAttributeFilter()) {
			return Mage::helper('fseo/layer')->formatUrlKey(
				$this->getFilter()->getAttributeModel()->getFrontend()->getOption($this->getValue())
			);
		}
		
		return null;
	}
	
	/**
	 * Determine whether this is the category filter
	 *
	 * @return bool
	 */
	public function isCategoryFilter()
	{
		return $this->getFilter() instanceof Mage_Catalog_Model_Layer_Filter_Category;
	}
	
	/**
	 * Determine whether this is the price filter
	 *
	 * @return bool
	 */
	public function isPriceFilter()
	{
		return $this->getFilter() instanceof Mage_Catalog_Model_Layer_Filter_Price;
	}
	
	/**
	 * Determine whether this is the attribute filter
	 *
	 * @return bool
	 */
	public function isAttributeFilter()
	{
		return $this->getFilter() instanceof Mage_Catalog_Model_Layer_Filter_Attribute;
	}
	
	/**
	 * Determine whether the filter is applied
	 *
	 * @return bool
	 */
	protected function _isApplied()
	{
		$values = Mage::app()->getRequest()->getParam(
			$this->getFilter()->getRequestVar()
		);
		
		return is_array($values)
			? in_array($this->getValue(), $values)
			: $this->getValue() === $values;
	}

	/**
	 * Determine whether the extension is enabled for the current entity type
	 *
	 * @return bool
	 */	
	public function isEnabled()
	{
		return Mage::helper('fseo/layer')->isEntityTypeEnabled();
	}
}
