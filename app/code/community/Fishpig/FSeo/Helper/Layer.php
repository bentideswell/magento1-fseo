<?php
/**
 * @category    Fishpig
 * @package    Fishpig_FSeo
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_FSeo_Helper_Layer extends Mage_Core_Helper_Abstract
{
	/**
	 * Cache for the currently applied page
	 *
	 * @var null|Fishpig_FSeo_Model_Catalog_Layer_Page
	 */
	protected $_appliedPage = null;
	
	/**
	 * Cache for enabled attributes
	 *
	 * @var null|array
	 */
	protected $_enabledAttributeCache = null;
	
	/**
	 *
	 *
	 * @return 
	**/
	public function getLayer()
	{
		if ($this->getEntityType() === 'attributeSplash_page') {
			return Mage::getSingleton('attributeSplash/layer');
		}
		else if ($this->getEntityType() === 'splash_page') {
			return Mage::getSingleton('splash/layer');			
		}
		
		return Mage::getSingleton('catalog/layer');
	}
	
	/**
	 * Get the base URL Key
	 * This is part of the URL that represents the entity (eg. category or Splash Page)
	 *
	 * @return string
	 */
	public function getEntityUrlKey()
	{
		if ($data = $this->getAppliedFilterData()) {
			return $data->getEntityUrlKey();
		}

		$requestUri = trim(Mage::app()->getRequest()->getOriginalRequest()->getPathInfo(), '/');
		
		return rtrim($this->getUrlSuffix(), '/') !== ''
			? substr($requestUri, 0, -strlen(rtrim($this->getUrlSuffix(), '/')))
			: $requestUri;
	}

	/**
	 * Get the URL suffix
	 *
	 * @return string
	 * @todo. use getEntityType() and get either category or Splash page URL suffix
	 */
	public function getUrlSuffix()
	{
		if ($data = $this->getAppliedFilterData()) {
			return $data->getUrlSuffix();
		}
		else if ($this->getEntityType() === 'attributeSplash_page') {
			return Fishpig_AttributeSplash_Model_Page::getUrlSuffix();
		}
		else if ($this->getEntityType() === 'splash_page') {
			return Fishpig_AttributeSplashPro_Model_Page::getUrlSuffix();
		}

		return Mage::helper('catalog/category')->getCategoryUrlSuffix();
	}

	/**
	 *
	 *
	 * @return 
	**/
	public function getEntityType()
	{
		if ($data = $this->getAppliedFilterData()) {
			return $data->getEntityType();
		}
		
		$entity = $this->getEntity();

		if ($entity instanceof Fishpig_AttributeSplash_Model_Page) {
			return 'attributeSplash_page';
		}
		else if ($entity instanceof Fishpig_AttributeSplashPro_Model_Page) {
			return 'splash_page';
		}
		else if ($entity instanceof Mage_Catalog_Model_Category) {
			return 'catalog_category';
		}

		return false;
	}

	/**
	 * Get the filter URL Key
	 * This is part of the URL that represents the applied filters (the bit after the base_url_key)
	 *
	 * @return string
	 */
	public function getFilterUrlKey()
	{
		if ($data = $this->getAppliedFilterData()) {
			return $data->getFilterUrlKey();
		}
		
		return null;
	}
	
	/**
	 * Get the URL Key
	 * This is the full URL key which consists of the base_url_key, filter_url_key and the url_suffix
	 *
	 * @return string
	 */
	public function getUrlKey()
	{
		if ($this->getFilterUrlKey()) {
			return $this->getEntityUrlKey() . '/' . $this->getFilterUrlKey() . $this->getUrlSuffix();
		}

		return $this->getEntityUrlKey() . $this->getUrlSuffix();		
	}
	
	/**
	 * Convert the given $urlKey into a full absolute URL
	 *
	 * @param string $urlKey = null
	 * @return string
	 */
	public function getUrl($urlKey = null)
	{
        return $this->_getUrl('*/*/*', array(
        	'_direct' => !is_null($urlKey) ? $urlKey : $this->getUrlKey(),
        	'_nosid' => true,
        ));
	}

	/**
	 * Get, cache and return the currently applied page
	 *
	 * @return false|Fishpig_FSeo_Model_Catalog_Layer_Page
	 */
	public function getAppliedPage()
	{
		if (is_null($this->_appliedPage)) {
			$this->_appliedPage = Mage::getResourceModel('fseo/catalog_layer_page')->getAppliedPage();
		}

		return $this->_appliedPage;
	}
	
	/** 
	 * Get the currently applied filter data (including attribute and other parent data)
	 *
	 * @return array
	 */
	public function getAppliedFilterData()
	{
		return ($filterData = Mage::registry('fseo_layer_applied_filter_data')) !== null
			? $filterData
			: false;
	}
	
	/** 
	 * Get the currently applied filter data
	 *
	 * @return array
	 */
	public function getAppliedFilters()
	{
		return ($data = $this->getAppliedFilterData()) ? $data->getFilters() : false;
	}

	/**
	 *
	 *
	 * @return 
	**/
	public function getEntityId()
	{
		return ($data = $this->getAppliedFilterData()) ? $data->getEntityId() : false;
	}
	
	/**
	 *
	 *
	 * @return 
	**/
	public function getEntityTree()
	{
		$object = $this->getEntity();
		$tree = array($object);
		$safe = 20;

		if ($this->getEntityType() === 'catalog_category') {
			while($object && $object->getParentCategory()->getLevel() >= 2 && --$safe > 0) {
				$object = $object->getParentCategory();
				$tree[$object->getId()] = $object;
			}
		}
		else if ($this->getEntityType() === 'attributeSplash_page') {
			if (Mage::helper('attributeSplash')->includeGroupUrlKeyInPageUrl()) {
				$tree[] = $object->getSplashGroup();
			}
		}
		
		return $tree;
	}

	/**
	 *
	 *
	 * @return 
	**/
	public function getObject()	
	{
		return $this->getEntity();
	}
	
	/**
	 *
	 *
	 * @return 
	**/
	public function getEntity()
	{
		if ($entity = Mage::registry('splash_page')) {
			return $entity;
		}
		else if ($entity = Mage::registry('current_category')) {
			return $entity;
		}
		
		return false;
	}

	/**
	 *
	 *
	 * @return 
	**/
	public function getAttributes()
	{
		$attributes = array();
		$allAttributes = Mage::getResourceModel('catalog/product_attribute_collection')
			->addIsFilterableFilter()
			->setOrder('position', 'ASC')
			->addOrder('attribute_code', 'ASC')
			->load();
		
		foreach($allAttributes as $key => $attribute) {
			$options = $attribute->getSource()->getAllOptions(false);
	
			foreach($options as $it => $option) {
				if (is_numeric(trim($option['label']))) {
					unset($options[$it]);
				}
			}
	
			if (count($options) > 0) {
				$attribute->setPrunedOptions($options);
				$attributes[] = $attribute;
			}
		}
		
		return count($attributes) > 0 ? $attributes : false;
	}

	/**
	 *
	 *
	 * @return 
	 **/
	public function formatUrlKey($s)
	{	
		$s = preg_replace('/([0-9]{1,})"/', '$1 Inch', $s);
		$s = str_replace(array("'"), '', $s);

		return Mage::getSingleton('catalog/product_url')->formatUrlKey($s);		
		return trim(preg_replace('/([^_a-z0-9-]{1,})/', '-', strtolower($s)), '-');
	}
	
	/**
	 *
	 *
	 * @return 
	 **/
	public function getStringJoiner()
	{
		return ' and ';
	}
	
	/**
	 * Determine whether the entity type is enabled
	 *
	 * @param string $entityType = null
	 * @return bool
	 */
	public function isEntityTypeEnabled($entityType = null)
	{
		if (is_null($entityType)) {
			$entityType = $this->getEntityType();
		}

		return in_array($entityType, explode(',', Mage::getStoreConfig('fseo/layer/enabled')));
	}
	
	/**
	 * Apply the rewrites so the layer works properly
	 *
	 * @return $this
	 */
	public function applyLayerRewrites()
	{
		Mage::getConfig()->setNode('global/models/catalog/rewrite/layer_filter_item', 'Fishpig_FSeo_Model_Catalog_Layer_Filter_Item', true);
		Mage::getConfig()->setNode('global/models/catalog/rewrite/layer_filter_item', 'Fishpig_FSeo_Model_Catalog_Layer_Filter_Item', true);
		Mage::getConfig()->setNode('global/models/catalog/rewrite/layer_filter_attribute', 'Fishpig_FSeo_Model_Catalog_Layer_Filter_Attribute', true);
		Mage::getConfig()->setNode('global/models/catalog/rewrite/layer_filter_category', 'Fishpig_FSeo_Model_Catalog_Layer_Filter_Category', true);
		
		Mage::getConfig()->setNode('global/models/catalog_resource/rewrite/layer_filter_item', 'Fishpig_FSeo_Model_Resource_Catalog_Layer_Filter_Attribute', true);
		Mage::getConfig()->setNode('global/models/catalog_resource_eav_mysql4/rewrite/layer_filter_item', 'Fishpig_FSeo_Model_Resource_Catalog_Layer_Filter_Attribute', true);
		
		return $this;
	}
	
	/**
	 * Determine whether the attribute code is enabled
	 *
	 * @param string $attributeCode
	 * @return bool
	 */
	public function isAttributeEnabled($attributeCode)
	{
		if (Mage::getStoreConfigFlag('fseo/layer/use_all_attributes')) {
			return true;
		}
		
		if (is_null($this->_enabledAttributeCache)) {
			$this->_enabledAttributeCache = explode(',', Mage::getStoreConfig('fseo/layer/use_specific_attributes'));
		}
		
		return in_array($attributeCode, $this->_enabledAttributeCache);
	}
	
	/**
	 * Convert the current URL to a new URL if possible
	 *
	 * @return string|false
	 */
	public function getNewUrlForCurrentUrl()
	{
		if (!$this->isEntityTypeEnabled()) {
			return false;
		}
		
		$entity = $this->getEntity();
		
		if ($query = Mage::app()->getRequest()->getQuery()) {
			$_attributes = $this->getAttributes();
			$urlParts = array();
			
			foreach($_attributes as $_attribute) {
				if (isset($query[$_attribute->getAttributeCode()])) {
					$value = (int)$query[$_attribute->getAttributeCode()];

					foreach($_attribute->getPrunedOptions() as $_option) {
						if ((int)$_option['value'] === $value) {
							$urlParts[] = $this->formatUrlKey($_option['label']);
							
							unset($query[$_attribute->getAttributeCode()]);
							break;
						}
					}
				}
			}
			
			if (count($urlParts) > 0) {
				$url = rtrim($entity->getUrl(), '/');
				$urlSuffix = $this->getUrlSuffix();
			
				if ($urlSuffix && $urlSuffix !== '/') {
					$url = substr($url, 0, -strlen($urlSuffix));
				}
				
				$url .= '/' . implode('/', $urlParts) . $urlSuffix;
				
				if (count($query) > 0) {
					$url .= '?' . http_build_query($query);
				}
				
				return $url;
			}
		}
		
		return false;
	}
}
