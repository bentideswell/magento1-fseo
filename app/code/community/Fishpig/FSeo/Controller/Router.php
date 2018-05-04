<?php
/**
 * @category    Fishpig
 * @package    Fishpig_FSeo
 * @license      http://fishpig.co.uk/license.txt
 * @author       Ben Tideswell <ben@fishpig.co.uk>
 */

class Fishpig_FSeo_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{
	/**
	 * Initialize Controller Router
	 *
	 * @param Varien_Event_Observer $observer
	*/
	public function initControllerRouters(Varien_Event_Observer $observer)
	{
		$observer->getEvent()
			->getFront()
				->addRouter('fseo', $this);

		Mage::helper('fseo/legacy')->applyLegacyHacks();
	}

    /**
     * Validate and Match Cms Page and modify request
     *
     * @param Zend_Controller_Request_Http $request
     * @return bool
     */
    public function match(Zend_Controller_Request_Http $request)
    {
    	try {
	    	if (($isMatched = $this->_matchForLayeredNavigation($request)) !== null) {
		    	return $isMatched;
	    	}
		}
		catch (Exception $e) {
			echo sprintf('<h1>%s</h1><pre>%s</pre>', $e->getMessage(), $e->getTraceAsString());
			exit;
		}

		return false;
	}
	
	protected function _matchForLayeredNavigation(Zend_Controller_Request_Http $request)
	{
		$_helper = Mage::helper('fseo/layer');

		$isEnabledForCatalogCategory = $_helper->isEntityTypeEnabled('catalog_category');

    	if (!$isEnabledForCatalogCategory || !($entityData = $this->_extractEntityDataFromUrl($request->getPathInfo()))) {
    		$transport = new Varien_Object(array(
    			'entity_data' => null
    		));
    		
    		Mage::dispatchEvent('fseo_layered_navigation_match_entity', array(
    			'transport' => $transport,
    			'request_uri' => trim($request->getPathInfo(), '/'), 
    			'request' => $request, 
    			'router' => $this,
    		));

			if (!($entityData = $transport->getEntityData())) {
		    	return null;
		    }
    	}

    	if (!($attributes = Mage::helper('fseo/layer')->getAttributes())) {
	    	return null;
    	}
 
    	$entityUrlKey = $entityData->getEntityUrlKey();
		$urlKey = $entityUrlKey;
		$tokens = $entityData->getTokens();

    	if (!($appliedFilters = $this->_processAttributeOptions($entityUrlKey, $attributes, $tokens))) {
    		return null;
    	}

		$filterUrlKey = array();

		foreach($appliedFilters as $attributeCode => $appliedFilter) {
			// Update URI
			$urlKey .= '/' . rtrim($appliedFilter['slug'], ',');
			
			// Build the filter URL key
			$filterUrlKey[] = rtrim($appliedFilter['slug'], ',');
			
			// Set query string param
			$request->setParam($appliedFilter['attribute_code'], $appliedFilter['values']);
			
			// Update URL
			$appliedFilter['url'] = $_helper->getUrl(rtrim($urlKey, '/') . $entityData->getUrlSuffix());			
			
			// Save option to filters list
			$appliedFilters[$attributeCode] = new Varien_Object($appliedFilter);
		}
		
		$result = new Varien_Object(array(
			'entity_type' => $entityData->getEntityType(),
			'entity_id' => $entityData->getEntityId(),
			'entity_url_key' => $entityData->getEntityUrlKey(),
			'filter_url_key' => implode('/', $filterUrlKey),
			'url_suffix' => $entityData->getUrlSuffix(),
			'url_key' => $urlKey,
			'filters' => $appliedFilters,
		));

		Mage::register('fseo_layer_applied_filter_data', $result);

		if ($page = Mage::helper('fseo/layer')->getAppliedPage()) {
			if (!$page->canDisplayOn($entityData->getEntityType())) {
				return false;
			}
		}
		
		// Create a template for the current request
		if (Mage::getStoreConfigFlag('fseo/layer/auto_create_templates')) {
			Mage::getResourceModel('fseo/catalog_layer_page')->createTemplateForRequest($result);
		}
		
		$request->setModuleName($entityData->getModuleName())
			->setControllerName($entityData->getControllerName())
			->setActionName($entityData->getActionName())
			->setParams($entityData->getParams());
		
		$request->setAlias(
			Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
			$urlKey . $entityData->getUrlSuffix()
		);

		Mage::dispatchEvent('fseo_router_match_url', array(
			'url_key' => $urlKey . $entityData->getUrlSuffix(),
			'router' => $this
		));

		return true;	
	}

	/**
	 * Take the requestUri, figure out the category it's for and parse it into parts
	 *
	 * @param string $requestUri
	 * @return false|Varien_Object
	 */
	protected function _extractEntityDataFromUrl($requestUri)
	{
    	$urlKey = trim($requestUri, '/');
		$urlSuffix = Mage::helper('fseo/layer')->getUrlSuffix();
		
    	if ($urlSuffix && $urlSuffix !== '/') {
			if (substr($urlKey, -strlen($urlSuffix)) !== $urlSuffix) {
				return false;
			}
			
			$urlKey = substr($urlKey, 0, -strlen($urlSuffix));
    	}

    	if (strpos($urlKey, '/') === false) {
	    	return false;
    	}

    	$firstToken = substr($urlKey, 0, strpos($urlKey, '/'));

    	$resource = Mage::getSingleton('core/resource');
    	$db = $resource->getConnection('core_read');
    	
    	$select = $db->select()
    		->from($resource->getTableName('core/url_rewrite'), array('category_id', 'request_path'))
#    		->where('? LIKE ' . (new Zend_Db_Expr("CONCAT(SUBSTR(request_path, 0, -" . strlen($urlSuffix) . "), '%')")), $urlKey)
    		->where('request_path LIKE ?', $firstToken . '%')
    		->where('store_id=?', Mage::app()->getStore()->getId())
    		->where('is_system=?', 1)
    		->where('category_id IS NOT NULL')
    		->where('product_id IS NULL');

   		if ($results = $db->fetchAll($select)) {
   			$winner = array('length' => false, 'result' => false);

			foreach($results as $result) {
				$bUrlKey = $urlSuffix ? substr($result['request_path'], 0, -strlen($urlSuffix)) . '/' : $result['request_path'];

				if (strpos($urlKey, $bUrlKey) === 0) {
					list($categoryId, $requestPath) = array_values($result);
					
					if ($winner['length'] === false || strlen($bUrlKey) > $winner['length']) {
						$winner['length'] = strlen($bUrlKey);
						$winner['result'] = $result;
					}
				}
			}
			
			if ($winner['length'] !== false) {
				list($categoryId, $requestPath) = array_values($winner['result']);
				
				$categoryUri = $urlSuffix ? substr($requestPath, 0, -strlen($urlSuffix)) : $requestPath;
				$tokens = explode('/', trim(substr($urlKey, strlen($categoryUri)), '/'));

				return new Varien_Object(array(
					'entity_id' => $categoryId,
					'entity_type' => 'catalog_category',
					'entity_url_key' => $categoryUri,
					'url_suffix' => $urlSuffix,
					'tokens' => $tokens,
					'module_name' => 'catalog',
					'controller_name' => 'category',
					'action_name' => 'view',
					'params' => array(
						'id' => $categoryId,
					)
				));
			}
		}

		return false;
	}
		
	/**
	 * Process the attributes to find a match for the next token
	 *
	 * @param string $attributes
	 * @param array $attributes
	 * @param array $tokens
	 * @returnfalse|array
	 */
	protected function _processAttributeOptions($baseUri, array $attributes, array $tokens)
	{
		$tokenParts = explode(',', array_shift($tokens));
		$helper = Mage::helper('fseo/layer');
		$isMultiselectAllowed = Mage::helper('fseo/layer')->isMultiselectAllowed();
		
		foreach($attributes as $attributeKey => $attribute) {
			if (!Mage::helper('fseo/layer')->isAttributeEnabled($attribute->getAttributeCode())) {
				continue;
			}

			$result = array(
				'attribute_code' => $attribute->getAttributeCode(),
				'slugs' => array(),
				'options' => array(),
				'values' => array(),
				'labels' => array(),
			);

			foreach($attribute->getPrunedOptions() as $option) {
				
				// If Multiselect disabled and more than 1 token part, return false
				if (!$isMultiselectAllowed && count($tokenParts) > 1) {
					return false;
				}
				
				// The token parts contains duplicates
				if (count($tokenParts) !== count(array_unique($tokenParts))) {
					return false;
				}
				
				foreach($tokenParts as $tokenKey => $token) {
					if (($optionSlug = $helper->formatUrlKey($option['label'])) !== $token) {
						continue;
					}

					$option['slug'] = $optionSlug;

					$result['slugs'][] = $option['slug'];
					$result['values'][] = $option['value'];
					$result['labels'][] = $option['label'];
					$result['options'][] = $option;
					
					if (count($tokenParts) === count($result['options'])) {
						$result['slug'] = implode(',', $result['slugs']);
						$result['label'] = implode(Mage::helper('fseo/layer')->getStringJoiner(), $result['labels']);

				    	if (count($tokens) > 0) {
							if (isset($attributes[$attributeKey+1])) {
								if ($match = $this->_processAttributeOptions($result['slug'], array_slice($attributes, $attributeKey+1), $tokens)) {
									return array_merge(array($attribute->getAttributeCode() => $result), $match);
								}
							}		    	
				    	}
				    	else {
					    	return array($attribute->getAttributeCode() => $result);
				    	}
				    }
			    }
			}
		}
		
		return false;
	}
}
